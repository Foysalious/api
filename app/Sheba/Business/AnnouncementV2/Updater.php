<?php namespace App\Sheba\Business\AnnouncementV2;

use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\Dal\Announcement\AnnouncementStatus;
use Sheba\Dal\Announcement\ScheduledFor;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    /** @var AnnouncementRepositoryInterface $announcementRepo */
    private $announcementRepo;
    /** @var CreatorRequester $creatorRequest */
    private $creatorRequest;

    /**
     * Creator constructor.
     * @param AnnouncementRepositoryInterface $announcement_repository
     */
    public function __construct(AnnouncementRepositoryInterface $announcement_repository)
    {
        $this->announcementRepo = $announcement_repository;
    }

    public function setRequest(CreatorRequester $creator_requester)
    {
        $this->creatorRequest = $creator_requester;
        return $this;
    }

    public function update()
    {
        $announcement = $this->creatorRequest->getAnnouncement();
        $status = $announcement->status;
        if ($status == AnnouncementStatus::EXPIRED) return false;
        $data = [
            'end_date' => $this->creatorRequest->getEndDate(),
            'end_time' => $this->creatorRequest->getEndTime(),
            'is_published' => $this->creatorRequest->getIsPublished()
        ];
        if ($status == AnnouncementStatus::SCHEDULED) {
            $data = [
                'type' => $this->creatorRequest->getType(),
                'title' => $this->creatorRequest->getTitle(),
                'short_description' => $this->creatorRequest->getShortDescription(),
                'long_description' => $this->creatorRequest->getDescription(),
                'target_type' => $this->creatorRequest->getTargetType(),
                'target_id' => $this->creatorRequest->getTargetIds(),
                'scheduled_for' => $this->creatorRequest->getScheduledFor(),
                'start_date' => $this->creatorRequest->getStartDate(),
                'start_time' => $this->creatorRequest->getStartTime()
            ];
        }
        $this->announcementRepo->update($announcement, $data);
        if ($announcement->scheduled_for === ScheduledFor::NOW) $this->sendNotification($announcement);
        return true;
    }

    private function sendNotification($announcement)
    {
        $members_ids = $announcement->business->getActiveBusinessMember()->pluck('member_id')->toArray();
        (new AnnouncementNotifications($members_ids, $announcement))->shoot();
    }
}
