<?php namespace App\Sheba\Business\AnnouncementV2;

use App\Models\Business;
use Sheba\Dal\Announcement\Announcement;
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
    /** @var Announcement $announcement */
    private $announcement;

    /**
     * Creator constructor.
     * @param AnnouncementRepositoryInterface $announcement_repository
     */
    public function __construct(AnnouncementRepositoryInterface $announcement_repository)
    {
        $this->announcementRepo = $announcement_repository;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setAnnouncement(Announcement $announcement)
    {
        $this->announcement = $announcement;
        return $this;
    }

    public function setRequest(CreatorRequester $creator_requester)
    {
        $this->creatorRequest = $creator_requester;
        return $this;
    }

    public function update()
    {
        if ($this->announcement->status == AnnouncementStatus::PUBLISHED) {
            $data = [
                'end_date' => $this->creatorRequest->getEndDate(),
                'end_time' => $this->creatorRequest->getEndTime(),
                'is_published' => $this->creatorRequest->getIsPublished()
            ];
            $this->announcementRepo->update($this->announcement, $data);
        } else {
            $data = [
                'type' => $this->creatorRequest->getType(),
                'title' => $this->creatorRequest->getTitle(),
                'short_description' => $this->creatorRequest->getShortDescription(),
                'long_description' => $this->creatorRequest->getDescription(),
                'is_published' => $this->creatorRequest->getIsPublished(),
                'target_type' => $this->creatorRequest->getTargetType(),
                'target_id' => $this->creatorRequest->getTargetIds(),
                'scheduled_for' => $this->creatorRequest->getScheduledFor(),
                'start_date' => $this->creatorRequest->getStartDate(),
                'start_time' => $this->creatorRequest->getStartTime(),
                'end_date' => $this->creatorRequest->getEndDate(),
                'end_time' => $this->creatorRequest->getEndTime(),
                'status' => $this->creatorRequest->getStatus(),
            ];
            $this->announcementRepo->update($this->announcement, $data);

            $announcement = $this->announcement->fresh();
            if ($announcement->scheduled_for === ScheduledFor::NOW) {
                (new TargetedNotification($this->business))->sendTargetedNotification($announcement);
            }
        }

        return true;
    }
}
