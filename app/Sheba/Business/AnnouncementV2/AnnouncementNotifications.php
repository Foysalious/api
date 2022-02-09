<?php namespace App\Sheba\Business\AnnouncementV2;

use App\Jobs\Business\SendAnnouncementNotificationToEmployee;
use App\Jobs\Business\SendAnnouncementPushNotificationToEmployee;

class AnnouncementNotifications
{
    private $memberIds;
    private $announcement;

    public function __construct($members_ids, $announcement)
    {
        $this->memberIds = $members_ids;
        $this->announcement = $announcement;
    }

    public function shoot()
    {
        dispatch(new SendAnnouncementNotificationToEmployee($this->memberIds, $this->announcement));
        foreach ($this->memberIds as $member) {
            dispatch(new SendAnnouncementPushNotificationToEmployee($member, $this->announcement));
        }
    }

}
