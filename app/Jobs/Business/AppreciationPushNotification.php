<?php namespace App\Jobs\Business;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use Sheba\Dal\Appreciation\Appreciation;
use App\Sheba\Business\BusinessQueue;
use Sheba\PushNotificationHandler;

class AppreciationPushNotification extends BusinessQueue
{
    private $pushNotification;
    private $appreciation;

    public function __construct(Appreciation $appreciation)
    {
        $this->appreciation = $appreciation;
        $this->pushNotification = new PushNotificationHandler();
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            /** @var BusinessMember $appreciation_receiver */
            $appreciation_receiver = $this->appreciation->receiver;
            /** @var Member $appreciation_receiver_member */
            $appreciation_receiver_member = $appreciation_receiver->member;

            /** @var BusinessMember $appreciation_giver */
            $appreciation_giver = $this->appreciation->giver;
            /** @var Member $appreciation_giver_member */
            $appreciation_giver_member = $appreciation_giver->member;
            /** @var Profile $appreciation_giver_member_profile */
            $appreciation_giver_member_profile = $appreciation_giver_member->profile;

            $topic = config('sheba.push_notification_topic_name.employee') . (int)$appreciation_receiver_member->id;
            $channel = config('sheba.push_notification_channel_name.employee');
            $sound = config('sheba.push_notification_sound.employee');

            $notification_data = [
                "title" => 'Appreciation',
                "message" => "$appreciation_giver_member_profile->name appreciated you",
                "event_type" => 'appreciation',
                "event_id" => $this->appreciation->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];

            $this->pushNotification->send($notification_data, $topic, $channel, $sound);
        }
    }
}