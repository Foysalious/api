<?php namespace App\Jobs\Business;

use App\Models\Member;
use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\Announcement\Announcement;
use Sheba\Dal\AnnouncementNotificationInfo\AnnouncementNotificationInfoRepositoryInterface;
use Sheba\PushNotificationHandler;

class SendAnnouncementPushNotificationToEmployee extends BusinessQueue
{
    /** @var Member */
    private $member;
    /** @var Announcement */
    private $announcement;
    private $pushNotification;
    /*** @var AnnouncementNotificationInfoRepositoryInterface $announcementNotificationInfoRepo*/
    private $announcementNotificationInfoRepo;

    public function __construct($member, Announcement $announcement)
    {
        $this->member = $member;
        $this->announcement = $announcement;
        $this->pushNotification = new PushNotificationHandler();
        $this->announcementNotificationInfoRepo = app(AnnouncementNotificationInfoRepositoryInterface::class);
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            $topic = config('sheba.push_notification_topic_name.employee') . $this->member;
            $channel = config('sheba.push_notification_channel_name.employee');
            $sound = config('sheba.push_notification_sound.employee');
            $this->pushNotification->send([
                "title" => 'New announcement arrived',
                "message" => $this->announcement->title,
                "event_type" => 'announcement',
                "event_id" => $this->announcement->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel, $sound);
            $announcement_notification = $this->announcementNotificationInfoRepo->where('announcement_id', $this->announcement->id)->where('member_id', $this->member)->first();
            $this->announcementNotificationInfoRepo->update($announcement_notification, ['queue_out' => 1]);
        }

    }
}
