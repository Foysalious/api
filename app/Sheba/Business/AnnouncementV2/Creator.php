<?php namespace App\Sheba\Business\AnnouncementV2;

use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\Dal\Announcement\Announcement;
use Sheba\PushNotificationHandler;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use App\Models\Business;

class Creator
{
    use ModificationFields;

    /** @var AnnouncementRepositoryInterface $announcementRepo */
    private $announcementRepo;
    /** @var PushNotificationHandler $pushNotification */
    private $pushNotification;
    /** @var CreatorRequester $creatorRequest */
    private $creatorRequest;
    private $business;
    private $businessMember;
    private $data = [];

    /**
     * Creator constructor.
     * @param AnnouncementRepositoryInterface $announcement_repository
     * @param PushNotificationHandler $push_notification
     */
    public function __construct(AnnouncementRepositoryInterface $announcement_repository, PushNotificationHandler $push_notification)
    {
        $this->announcementRepo = $announcement_repository;
        $this->pushNotification = $push_notification;
    }


    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setRequest(CreatorRequester $creator_requester)
    {
        $this->creatorRequest = $creator_requester;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        /** @var Announcement $announcement */
        $announcement = $this->announcementRepo->create($this->data);

        return $announcement;
    }

    private function makeData()
    {
        $this->data = $this->withCreateModificationField([
            'business_id' => $this->business->id,
            'type' => $this->creatorRequest->getType(),
            'title' => $this->creatorRequest->getTitle(),
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
        ]);
    }
}