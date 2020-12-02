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

class ProcessSmsCampaignStatusJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

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

        if ($this->isSuccessfullySent()) {
            $this->campaignOrderReceiver->status = Status::SUCCESSFUL;
            $this->campaignOrderReceiver->save();
            return;
        }

        if ($this->isPending()) {
            $this->campaignOrderReceiver->status = Status::PENDING;
            $this->campaignOrderReceiver->save();
        } elseif ($this->campaignOrderReceiver->isNotFailed()) {
            $this->campaignOrderReceiver->status = Status::FAILED;
            $this->campaignOrderReceiver->save();
            $this->refund();
        }
    }

    private function isSuccessfullySent()
    {
        return $this->checkStatus('DELIVERED');
    }

    private function getOrderStatus()
    {
        $response = $this->smsHandler->getSingleMessage($this->campaignOrderReceiver->message_id);
        return $response ? $response->status->name : 'PENDING';
    }

    private function isPending()
    {
        return $this->checkStatus('PENDING');
    }

    private function checkStatus($status)
    {
        return (bool)(strpos($this->getOrderStatus(), $status) !== false);
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
