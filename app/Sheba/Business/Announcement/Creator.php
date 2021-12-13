<?php namespace Sheba\Business\Announcement;

use App\Jobs\Business\SendAnnouncementNotificationToEmployee;
use App\Jobs\Business\SendAnnouncementPushNotificationToEmployee;
use App\Models\Business;
use App\Models\BusinessMember;
use Carbon\Carbon;
use Sheba\Business\CoWorker\Statuses;
use Sheba\Dal\Announcement\Announcement;
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\ModificationFields;
use Sheba\PushNotificationHandler;

class Creator
{
    use ModificationFields;

    private $announcementRepository;
    private $title;
    private $shortDescription;
    private $longDescription;
    private $type;
    private $data;
    /** @var Carbon */
    private $endDate;
    /** @var Business */
    private $business;
    /** @var PushNotificationHandler */
    private $pushNotification;
    private $businessMember;

    /**
     * Creator constructor.
     * @param AnnouncementRepositoryInterface $announcement_repository
     * @param PushNotificationHandler $push_notification
     */
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
     * @param mixed $short_description
     * @return Creator
     */
    public function setShortDescription($short_description)
    {
        $this->shortDescription = $short_description;
        return $this;
    }

    /**
     * @param $long_description
     * @return Creator
     */
    public function setLongDescription($long_description)
    {
        $this->longDescription = $long_description;
        return $this;
    }

    /**
     * @param string $endDate
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
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return Creator
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->setModifier($this->businessMember->member);
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

        $members_ids = $this->business->getActiveBusinessMember()->pluck('member_id')->toArray();
        dispatch(new SendAnnouncementNotificationToEmployee($members_ids, $announcement));
        foreach ($members_ids as $member) {
            dispatch(new SendAnnouncementPushNotificationToEmployee($member, $announcement));
        }

        return $announcement;
    }

    private function makeData()
    {
        $this->data = $this->withCreateModificationField([
            'business_id' => $this->business->id,
            'title' => $this->title,
            'short_description' => $this->shortDescription,
            'long_description' => $this->longDescription,
            'end_date' => $this->endDate
        ]);

        if ($this->type) $this->data['type'] = $this->type;
    }
}
