<?php namespace Sheba\Business\NotificationHistory;

use Sheba\Dal\BusinessNotificationHistory\BusinessNotificationHistoryRepository;
use Sheba\Dal\BusinessNotificationHistory\BusinessNotificationHistory;
use App\Models\BusinessMember;

class Updater
{
    /**  @var BusinessNotificationHistoryRepository $businessNotificationHistoryRepo */
    private $businessNotificationHistoryRepo;
    /** @var BusinessMember $businessMember */
    private $businessMember;
    private $status;
    private $statusData = [];

    public function __construct(BusinessNotificationHistoryRepository $business_notification_history_repo)
    {
        $this->businessNotificationHistoryRepo = $business_notification_history_repo;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function updateStatus()
    {
        $this->businessNotificationHistoryRepo->create($this->makeStatusData());
    }

    /**
     * @return mixed
     */
    public function makeStatusData()
    {
        return $this->statusData['status'] = $this->status;
    }
}