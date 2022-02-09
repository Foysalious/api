<?php namespace App\Sheba\Business\AnnouncementV2;

use App\Jobs\Business\SendAnnouncementNotificationToEmployee;
use App\Jobs\Business\SendAnnouncementPushNotificationToEmployee;
use Sheba\Dal\AnnouncementNotificationInfo\AnnouncementNotificationInfoRepositoryInterface;

class AnnouncementNotifications
{
    private $memberIds;
    private $announcement;
    /*** @var AnnouncementNotificationInfoRepositoryInterface $announcementNotificationInfoRepo*/
    private $announcementNotificationInfoRepo;

    public function __construct($members_ids, $announcement)
    {
        $this->memberIds = $members_ids;
        $this->announcement = $announcement;
        $this->announcementNotificationInfoRepo = app(AnnouncementNotificationInfoRepositoryInterface::class);
    }

    public function shoot()
    {
        dispatch(new SendAnnouncementNotificationToEmployee($this->memberIds, $this->announcement));

        foreach ($this->memberIds as $member) {
            $this->announcementNotificationInfoRepo->create([
                'announcement_id' => $this->announcement->id,
                'member_id' => $member
            ]);
            dispatch(new SendAnnouncementPushNotificationToEmployee($member, $this->announcement));
        }
    }

}
