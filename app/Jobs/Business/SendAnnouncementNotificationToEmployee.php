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
        if ($this->attempts() < 2) {
            notify()->members($this->members)->send([
                'title' => $this->announcement->title,
                'type' => 'warning',
                'event_type' => get_class($this->announcement),
                'event_id' => $this->announcement->id
            ]);
        }
    }
}
