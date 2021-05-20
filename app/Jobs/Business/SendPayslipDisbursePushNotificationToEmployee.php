<?php namespace App\Jobs\Business;

use App\Models\BusinessMember;
use App\Sheba\Business\BusinessQueue;
use Sheba\PushNotificationHandler;

class SendPayslipDisbursePushNotificationToEmployee extends BusinessQueue
{
    /**
     * @var PushNotificationHandler
     */
    private $pushNotification;
    /**
     * @var BusinessMember
     */
    private $businessMember;

    public function __construct(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->pushNotification = new PushNotificationHandler();
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            $topic = config('sheba.push_notification_topic_name.employee') . (int)$this->businessMember->member->id;
            $channel = config('sheba.push_notification_channel_name.employee');
            $sound  = config('sheba.push_notification_sound.employee');
            $notification_data = [
                "title" => 'Payslip Disbursed',
                "message" => "Payslip Has been Disbursed",
                "event_type" => 'payslip',
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];
            $this->pushNotification->send($notification_data, $topic, $channel, $sound);
        }
    }

}