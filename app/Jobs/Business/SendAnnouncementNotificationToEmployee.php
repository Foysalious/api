<?php namespace App\Jobs\Business;

use App\Models\Member;
use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\Announcement\Announcement;

class SendAnnouncementNotificationToEmployee extends BusinessQueue
{
    /** @var Member[] */
    private $members;
    /** @var Announcement */
    private $announcement;

    public function __construct($members, Announcement $announcement)
    {
        $this->members = $members;
        $this->announcement = $announcement;
        parent::__construct();
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
