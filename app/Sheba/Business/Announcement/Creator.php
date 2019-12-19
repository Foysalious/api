<?php namespace Sheba\Business\Announcement;


use App\Jobs\Business\SendAnnouncementNotificationToEmployee;
use App\Models\Business;
use Carbon\Carbon;
use Sheba\Dal\Announcement\Announcement;
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\PushNotificationHandler;

class Creator
{
    private $announcementRepository;
    private $title;
    private $shortDescription;
    private $type;
    private $data;
    /** @var Carbon */
    private $endDate;
    /** @var Business */
    private $business;
    /** @var PushNotificationHandler */
    private $pushNotification;

    public function __construct(AnnouncementRepositoryInterface $announcement_repository, PushNotificationHandler $push_notification)
    {
        $this->announcementRepository = $announcement_repository;
        $this->pushNotification = $push_notification;
    }

    /**
     * @param mixed $title
     * @return Creator
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param mixed $shortDescription
     * @return Creator
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    /**
     * @param Carbon $endDate
     * @return Creator
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @param Business $business
     * @return Creator
     */
    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        /** @var Announcement $announcement */
        $announcement = $this->announcementRepository->create($this->data);
        $this->business->load(['members' => function ($q) {
            $q->select('members.id', 'profile_id');
        }]);
        dispatch((new SendAnnouncementNotificationToEmployee($this->business->members, $announcement)));
        return $announcement;
    }

    private function makeData()
    {
        $this->data = [
            'business_id' => $this->business->id,
            'title' => $this->title,
            'short_description' => $this->shortDescription,
            'end_date' => $this->endDate->toDateTimeString()
        ];
        if ($this->type) $this->data['type'] = $this->type;
    }
}