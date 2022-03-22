<?php namespace App\Sheba\Business\AnnouncementV2;

use Sheba\Dal\Announcement\AnnouncementTarget;
use Sheba\Dal\Announcement\Announcement;
use App\Models\BusinessMember;
use App\Models\Business;

class TargetedNotification
{
    /**  @var Business $business */
    private $business;

    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    public function sendTargetedNotification(Announcement $announcement)
    {
        $members_ids = [];
        if ($announcement->target_type === AnnouncementTarget::ALL) {
            $members_ids = $this->getAllMemberIDs();
        }
        if ($announcement->target_type === AnnouncementTarget::DEPARTMENT) {
            $members_ids = $this->getMemberIDsByDepartment($announcement);
        }
        if ($announcement->target_type === AnnouncementTarget::EMPLOYEE) {
            $members_ids = $this->getMemberIDsByBusinessMember($announcement);
        }
        if ($announcement->target_type === AnnouncementTarget::EMPLOYEE_TYPE) {
            $members_ids = $this->getMemberIDsByEmployeeType($announcement);
        }

        (new AnnouncementNotifications($members_ids, $announcement))->shoot();
    }

    private function getAllMemberIDs()
    {
        return $this->business->getActiveBusinessMember()->pluck('member_id')->toArray();
    }

    private function getMemberIDsByEmployeeType($announcement)
    {
        $business_members = $this->business->getActiveBusinessMember();
        $target_ids = json_decode($announcement->target_id, 1);
        $business_members = $business_members->whereIn('employee_type', $target_ids);

        return $business_members->pluck('member_id')->toArray();
    }

    private function getMemberIDsByBusinessMember($announcement)
    {
        $target_ids = json_decode($announcement->target_id, 1);
        $business_members = BusinessMember::find($target_ids);

        return $business_members->pluck('member_id')->toArray();
    }

    private function getMemberIDsByDepartment($announcement)
    {
        $business_members = $this->business->getActiveBusinessMember();
        $target_ids = json_decode($announcement->target_id, 1);

        $business_members = $business_members->whereHas('role', function ($q) use ($target_ids) {
            $q->whereHas('businessDepartment', function ($q) use ($target_ids) {
                $q->whereIn('business_departments.id', $target_ids);
            });
        });

        return $business_members->pluck('member_id')->toArray();
    }
}