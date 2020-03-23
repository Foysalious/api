<?php namespace Sheba\SmsCampaign\Jobs;

use App\Jobs\Job;
use App\Models\SmsCampaignOrderReceiver;
use App\Sheba\SmsCampaign\SmsHandler;
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
        $this->refund                = new Refund();
        $this->connection = 'sms_campaign';
        $this->queue      = 'sms_campaign';
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
        $this->campaignOrderReceiver->reload();
        if ($this->attempts() < 2) {
            if ($this->isSuccessfullySent($handler)) {
                $this->campaignOrderReceiver->status = constants('SMS_CAMPAIGN_RECEIVER_STATUSES.successful');
                $this->campaignOrderReceiver->save();
            } else {
                if ($this->isPending($handler)) {
                    $this->campaignOrderReceiver->status = constants('SMS_CAMPAIGN_RECEIVER_STATUSES.pending');
                    $this->campaignOrderReceiver->save();
                } elseif ($this->campaignOrderReceiver->status !== constants('SMS_CAMPAIGN_RECEIVER_STATUSES.failed')) {
                    $this->campaignOrderReceiver->status = constants('SMS_CAMPAIGN_RECEIVER_STATUSES.failed');
                    $this->campaignOrderReceiver->save();
                    $refund_receiver = $this->campaignOrderReceiver->smsCampaignOrder->partner;
                    $sms_count       = $this->campaignOrderReceiver->sms_count;
                    $this->refund->setRefundReceiver($refund_receiver)->setNumberOfSms($sms_count)->adjustWallet();
                    $this->deductLog($sms_count * constants('SMS_CAMPAIGN.rate_per_sms'));
                }
            }
        }
    }

    private function isSuccessfullySent(SmsHandler $handler)
    {
        if (strpos($this->getOrderStatus($handler), 'DELIVERED') !== false)
            return true;
        return false;
    }

    private function getOrderStatus(SmsHandler $sms_handler)
    {
        $response = $sms_handler->getSingleMessage($this->campaignOrderReceiver->message_id);
        if ($response)
            return $response->status->name;
        return 'PENDING';
    }

    private function isPending(SmsHandler $handler)
    {
        if (strpos($this->getOrderStatus($handler), 'PENDING') !== false)
            return true;
        return false;
    }

    private function deductLog($amount)
    {
        /** @var AutomaticEntryRepository $entry */
        $entry = app(AutomaticEntryRepository::class);
        $entry->setAmount($amount)->setPartner($this->campaignOrderReceiver->smsCampaignOrder->partner)->setHead(AutomaticExpense::SMS)->setSourceType(class_basename($this->campaignOrderReceiver->smsCampaignOrder))->setSourceId($this->campaignOrderReceiver->smsCampaignOrder->id)->deduct();
    }
}
