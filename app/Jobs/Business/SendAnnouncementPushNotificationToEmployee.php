<?php namespace App\Jobs\Business;

use App\Models\Member;
use App\Sheba\Business\BusinessQueue;
use Exception;
use Sheba\Dal\Announcement\Announcement;
use Sheba\PushNotificationHandler;
use Throwable;

class SendAnnouncementPushNotificationToEmployee extends BusinessQueue
{
    /** @var Member */
    private $member;
    /** @var Announcement */
    private $announcement;
    private $pushNotification;

    public function __construct($member, Announcement $announcement)
    {
        $this->member = $member;
        $this->announcement = $announcement;
        $this->pushNotification = new PushNotificationHandler();
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            try {
                $topic = config('sheba.push_notification_topic_name.employee') . $this->member;
                $channel = config('sheba.push_notification_channel_name.employee');
                $sound  = config('sheba.push_notification_sound.employee');
                $this->pushNotification->send([
                    "title" => 'New announcement arrived',
                    "message" => $this->announcement->title,
                    "event_type" => 'announcement',
                    "event_id" => $this->announcement->id,
                    "sound" => "notification_sound",
                    "channel_id" => $channel,
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                ], $topic, $channel, $sound);
            } catch (Throwable $e) {
                app('sentry')->captureException($e);
            }
        }
    }
}
