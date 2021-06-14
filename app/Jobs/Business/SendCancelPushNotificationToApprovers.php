<?php namespace App\Jobs\Business;

use App\Models\Profile;
use App\Sheba\Business\BusinessQueue;
use Sheba\PushNotificationHandler;
use App\Models\Member;

class SendCancelPushNotificationToApprovers extends BusinessQueue
{
    private $approver;
    private $leave;
    private $member;
    private $profile;
    /**
     * @var PushNotificationHandler
     */
    private $pushNotification;

    /**
     * SendCancelPushNotificationToApprovers constructor.
     * @param $approver
     * @param $leave
     * @param Profile $profile
     */
    public function __construct($approver, $leave, Profile $profile)
    {
        $this->approver = $approver;
        $this->leave = $leave;
        /**@var Member $member */
        $this->member = $this->approver->member;
        $this->profile = $profile;
        $this->pushNotification = new PushNotificationHandler();
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            $topic = config('sheba.push_notification_topic_name.employee') . $this->member->id;
            $channel = config('sheba.push_notification_channel_name.employee');
            $sound  = config('sheba.push_notification_sound.employee');
            $name = $this->profile->name ?: null;
            $data = [
                "title" => "Leave Cancel",
                "message" => $name . " canceled his leave",
                "event_type" => 'leave',
                "event_id" => $this->leave->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];
            $this->pushNotification->send($data, $topic, $channel, $sound);
        }
    }
}
