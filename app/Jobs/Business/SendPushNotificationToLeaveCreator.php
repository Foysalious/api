<?php namespace App\Jobs\Business;

use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use App\Sheba\Business\BusinessQueue;
use Sheba\PushNotificationHandler;

class SendPushNotificationToLeaveCreator extends BusinessQueue
{
    private $pushNotification;

    public function __construct($leave)
    {
        $this->leave = $leave;
        $this->pushNotification = new PushNotificationHandler();
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            $status = LeaveStatusPresenter::statuses()[$this->leave->status];
            $business_member = $this->leave->businessMember;

            $topic = config('sheba.push_notification_topic_name.employee') . $business_member->member->id;
            $channel = config('sheba.push_notification_channel_name.employee');
            $sound = config('sheba.push_notification_sound.employee');
            $push_notification_data = [
                "title" => 'Leave request update',
                "message" => "Your leave request has been $status",
                "event_type" => 'leave',
                "event_id" => $this->leave->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];

            $this->pushNotification->send($push_notification_data, $topic, $channel, $sound);
        }
    }
}