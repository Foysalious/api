<?php namespace App\Jobs;


use App\Models\BusinessMember;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\PushNotificationHandler;
class SendCancelPushNotificationToApprovers extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $approver;
    /**
     * @var PushNotificationHandler
     */
    private $pushNotification;
    private $profile;

    /**
     * SendCancelPushNotificationToApprovers constructor.
     * @param $approver
     * @param $profile
     */
    public function __construct($approver, $profile)
    {
        $this->approver = $approver;
        $this->profile = $profile;
        $this->pushNotification = new PushNotificationHandler();

    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            $topic = config('sheba.push_notification_topic_name.employee') . $this->approver->approver_id;
            $channel = config('sheba.push_notification_channel_name.employee');
            $data = [
                "title" => "leave cancel",
                "message" => "$this->profile canceled his leave",
                "event_type" => 'leave',
                "event_id" => $this->support->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];
            $this->pushNotification->send($data, $topic, $channel);
        }
    }

}
