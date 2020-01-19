<?php namespace App\Jobs\Business;


use App\Jobs\Job;
use App\Models\Member;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Dal\Announcement\Announcement;
use Sheba\PushNotificationHandler;

class SendAnnouncementNotificationToEmployee extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /** @var Member[] */
    private $members;
    /** @var Announcement */
    private $announcement;
    private $pushNotification;

    public function __construct($members, Announcement $announcement)
    {
        $this->members = $members;
        $this->announcement = $announcement;
        $this->pushNotification = new PushNotificationHandler();
    }

    public function handle()
    {
        if ($this->attempts() <= 1) {
            foreach ($this->members as $member) {
                notify()->member($member)->send([
                    'title' => $this->announcement->title,
                    'type' => 'warning',
                    'event_type' => get_class($this->announcement),
                    'event_id' => $this->announcement->id
                ]);
                $topic = config('sheba.push_notification_topic_name.employee') . $member->id;
                $channel = config('sheba.push_notification_channel_name.employee');
                $this->pushNotification->send([
                    "title" => 'New announcement arrived',
                    "message" => $this->announcement->title,
                    "event_type" => 'announcement',
                    "event_id" => $this->announcement->id,
                    "sound" => "notification_sound",
                    "channel_id" => $channel,
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                ], $topic, $channel);
            }
        }
    }
}