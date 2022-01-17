<?php namespace App\Jobs\Business;

use App\Models\BusinessMember;
use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\Support\Model as Support;
use Sheba\PushNotificationHandler;

class SendSupportPushNotificationToSuperAdminEmployee extends BusinessQueue
{
    /** @var PushNotificationHandler $pushNotification */
    private $pushNotification;
    /** @var BusinessMember $businessMember */
    private $businessMember;
    private $title;
    private $support;

    /**
     * SendSupportPushNotificationToSuperAdminEmployee constructor.
     * @param BusinessMember $business_member
     * @param $title
     * @param $support
     */
    public function __construct(BusinessMember $business_member, $title, Support $support)
    {
        $this->businessMember = $business_member;
        $this->title = $title;
        $this->support = $support;
        $this->pushNotification = new PushNotificationHandler();
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            $topic = config('sheba.push_notification_topic_name.employee') . $this->businessMember->member->id;
            $channel = config('sheba.push_notification_channel_name.employee');
            $sound  = config('sheba.push_notification_sound.employee');
            $data = [
                "title" => 'New support created',
                "message" => $this->title,
                "event_type" => 'support',
                "event_id" => $this->support->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];
            $this->pushNotification->send($data, $topic, $channel, $sound);
        }
    }
}
