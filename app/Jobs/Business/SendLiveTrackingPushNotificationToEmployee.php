<?php namespace App\Jobs\Business;

use App\Sheba\Business\BusinessQueue;
use Sheba\PushNotificationHandler;

class SendLiveTrackingPushNotificationToEmployee extends BusinessQueue
{
    const STATUS = [0 => 'Disabled', 1 => 'Enabled'];

    private $pushNotification;
    private $memberId;
    private $isEnabled;

    public function __construct($member_id, $is_enabled)
    {
        $this->memberId  = $member_id;
        $this->isEnabled = $is_enabled;
        $this->pushNotification = new PushNotificationHandler();
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            $topic = config('sheba.push_notification_topic_name.employee') . (int)$this->memberId;
            $channel = config('sheba.push_notification_channel_name.employee');
            $sound  = config('sheba.push_notification_sound.employee');
            $notification_data = [
                "title" => 'Live Location Tracking '.self::STATUS[$this->isEnabled],
                "message" => "Your live location tracking has been '.self::STATUS[$this->isEnabled].' according to your company policy",
                "event_type" => 'live_tracking',
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];

            $this->pushNotification->send($notification_data, $topic, $channel, $sound);
        }
    }
}
