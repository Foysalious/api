<?php namespace Sheba\SmsCampaign;

use App\Jobs\Job;
use App\Models\SmsCampaignOrderReceiver;
use App\Sheba\SmsCampaign\InfoBip\SmsHandler;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\SmsCampaign\SmsLogs;

class ProcessSmsCampaignStatuses extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var SmsCampaignOrderReceiver $campaignOrderReceiver */
    private $campaignOrderReceiver;
    /** @var SmsHandler $smsHandler */
    private $smsHandler;

    /**
     * Create a new job instance.
     *
     * @param SmsCampaignOrderReceiver $campaign_order_receiver
     * @param SmsHandler $smsHandler
     */
    public function __construct(SmsCampaignOrderReceiver $campaign_order_receiver)
    {
        $this->campaignOrderReceiver = $campaign_order_receiver;
        $this->connection = 'sms_campaign_queue';
        $this->queue = 'sms_campaign_queue';
    }

    /**
     * Execute the job.
     *
     * @param SmsHandler $handler
     * @return void
     */
    public function handle(SmsHandler $handler)
    {
        if ($this->attempts() < 2) {
            if ($this->isSuccessfullySent($handler)) {
                $this->campaignOrderReceiver->status = constants('SMS_CAMPAIGN_RECEIVER_STATUSES.successful');
                $this->campaignOrderReceiver->save();
            } else {
                $this->campaignOrderReceiver->status = constants('SMS_CAMPAIGN_RECEIVER_STATUSES.failed');
                $this->campaignOrderReceiver->save();
                $this->campaignOrderReceiver->refundIfFailed();
            }
        }
    }

    private function getOrderStatus(SmsHandler $sms_handler)
    {
        return ($sms_handler->getSingleMessage($this->campaignOrderReceiver->message_id))['status']['name'];
    }

    private function isSuccessfullySent(SmsHandler $handler)
    {
        if (strpos($this->getOrderStatus($handler), 'DELIVERED') !== false)
            return true;
        return false;
    }
}
