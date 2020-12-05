<?php namespace Sheba\SmsCampaign\Jobs;

use App\Jobs\Job;
use App\Models\Partner;
use Sheba\Dal\SmsCampaignOrderReceiver\SmsCampaignOrderReceiver;
use Sheba\Dal\SmsCampaignOrderReceiver\Status;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\SmsCampaign\SmsHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\SmsCampaign\Refund;

class CampaignSmsStatusChangeJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    const PENDING_THRESHOLD_DAYS = 7;

    /** @var SmsCampaignOrderReceiver $campaignOrderReceiver */
    private $campaignOrderReceiver;
    /** @var SmsHandler $smsHandler */
    private $smsHandler;
    /** @var Refund $refund */
    private $refund;

    /**
     * Create a new job instance.
     *
     * @param SmsCampaignOrderReceiver $campaign_order_receiver
     */
    public function __construct(SmsCampaignOrderReceiver $campaign_order_receiver)
    {
        $this->campaignOrderReceiver = $campaign_order_receiver;
        $this->refund                = app(Refund::class);
        $this->connection            = 'sms_campaign';
        $this->queue                 = 'sms_campaign';
    }

    /**
     * Execute the job.
     *
     * @param SmsHandler $handler
     * @return void
     * @throws \Exception
     */
    public function handle(SmsHandler $handler)
    {
        if ($this->attempts() >= 2) return;

        $this->smsHandler = $handler;
        $this->campaignOrderReceiver->reload();

        $status = $this->resolveNewStatus();
        if (!$status) return;

        $this->campaignOrderReceiver->status = $status;
        $this->campaignOrderReceiver->save();
        if ($status == Status::FAILED) $this->refund();
    }

    private function resolveNewStatus()
    {
        $sms = $this->smsHandler->getSingleMessage($this->campaignOrderReceiver->message_id);

        if ($sms->isSuccessful()) return Status::SUCCESSFUL;

        if ($sms->isPending()) {
            return $this->campaignOrderReceiver->isPendingForDays(self::PENDING_THRESHOLD_DAYS) ?
                Status::DELIVERED :
                Status::PENDING;
        }

        if ($this->campaignOrderReceiver->isNotFailed()) return Status::FAILED;

        return null;
    }

    /**
     * @throws ExpenseTrackingServerError
     */
    private function refund()
    {
        /** @var Partner $refund_receiver */
        $refund_receiver = $this->campaignOrderReceiver->smsCampaignOrder->partner;
        $sms_count       = $this->campaignOrderReceiver->sms_count;
        $this->refund->setRefundReceiver($refund_receiver)->setNumberOfSms($sms_count)->adjustWallet();
        $this->deductLog($sms_count * constants('SMS_CAMPAIGN.rate_per_sms'));
    }

    /**
     * @param $amount
     * @throws ExpenseTrackingServerError
     */
    private function deductLog($amount)
    {
        /**
         * @var AutomaticEntryRepository $entry
         * @var Partner $partner
         */
        $entry = app(AutomaticEntryRepository::class);
        $partner = $this->campaignOrderReceiver->smsCampaignOrder->partner;
        $entry->setAmount($amount)
            ->setPartner($partner)
            ->setHead(AutomaticExpense::SMS)
            ->setSourceType(class_basename($this->campaignOrderReceiver->smsCampaignOrder))
            ->setSourceId($this->campaignOrderReceiver->smsCampaignOrder->id)
            ->deduct();
    }
}
