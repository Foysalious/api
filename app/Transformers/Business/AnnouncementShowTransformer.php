<?php namespace App\Transformers\Business;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\Member;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Announcement\Announcement;

class AnnouncementShowTransformer extends TransformerAbstract
{
    const ALL = "all";
    const DEPARTMENT = "department";
    const EMPLOYEE = "employee";
    const EMPLOYEE_TYPE = "employee_type";

    const NOW = 'now';
    const LATER = 'later';

    const PUBLISHED = 'published';
    const SCHEDULED = 'scheduled';
    const EXPIRED = 'expired';

    public function transform(Announcement $announcement)
    {
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'description' => $announcement->long_description,
            'type' => ucfirst($announcement->type),
            'is_published' => $announcement->is_published,
            'target' => [
                'type' => $announcement->target_type,
                'type_ids' => $this->targetTypeInfo($announcement),
                'count' => $this->getCount($announcement),
            ],
            'active_between' => $this->getActiveBetween($announcement),
            'status' => $this->getStatus($announcement),
            'scheduled_info' => [
                'scheduled_for' => $announcement->scheduled_for,
                'start_date' => $announcement->scheduled_for == self::LATER ? $announcement->start_date : null,
                'start_time' => $announcement->scheduled_for == self::LATER ? $announcement->start_time : null,
                'end_date' => $announcement->end_date->toDateString(),
                'end_time' => $announcement->end_time,
            ],
            'created_by' => $this->getCreatedByNameAndId($announcement),
            'created_at' => $announcement->created_at->format('h:i A d/m/Y'),
            'updated_by' => $this->getUpdatedByNameAndId($announcement),
            'updated_at' => $announcement->updated_at->format('h:i A d/m/Y'),
        ];
    }

    private function getCreatedByNameAndId($announcement)
    {
        $member = $this->getMember($announcement->created_by);
        if (!$member) return "N/S";
        if ($member) {
            $profile = $member->profile;
            $business_member = $member->activeBusinessMember()->first();
            return $profile->name . ', ' . $business_member->employee_id;
        }
    }

    private function getUpdatedByNameAndId($announcement)
    {
        $member = $this->getMember($announcement->updated_by);
        if (!$member) return "N/S";
        if ($member) {
            $profile = $member->profile;
            $business_member = $member->activeBusinessMember()->first();
            return $profile->name . ', ' . $business_member->employee_id;
        }
    }

    private function getMember($member_id)
    {
        return Member::find($member_id);
    }

    private function getActiveBetween($announcement)
    {
        $start_date = Carbon::parse($announcement->start_date)->format('M d,Y');
        $end_date = Carbon::parse($announcement->start_date)->format('M d,Y');
        return $start_date . ' - ' . $end_date;
    }

    private function targetTypeInfo($announcement)
    {
        if ($announcement->target_type == self::ALL) return 'all';
        if ($announcement->target_type == self::EMPLOYEE) return $this->getEmployeeInfo($announcement);
        if ($announcement->target_type == self::DEPARTMENT) return $this->getDepartmentInfo($announcement);
        if ($announcement->target_type == self::EMPLOYEE_TYPE) return $this->getEmployeeTypeInfo($announcement);
    }

    private function getCount($announcement)
    {
        if ($announcement->target_type == self::ALL) return 0;
        if ($announcement->target_type == self::EMPLOYEE) return count($this->getDecodedTarget($announcement));
        if ($announcement->target_type == self::DEPARTMENT) return count($this->getDecodedTarget($announcement));
        if ($announcement->target_type == self::EMPLOYEE_TYPE) return count($this->getDecodedTarget($announcement));
    }


    private function getDecodedTarget($announcement)
    {
        return json_decode($announcement->target_id, 1);
    }

    private function getEmployeeInfo($announcement)
    {
        $employee_info = [];
        $business_member_ids = $this->getDecodedTarget($announcement);
        foreach ($business_member_ids as $business_member_id) {
            $business_member = BusinessMember::find($business_member_id);
            $employee_info [] = [
                'business_member_id' => $business_member->id,
                'name' => $business_member->profile()->name,
                'pro_pic' => $business_member->profile()->pro_pic,
                'employee_id' => $business_member->employee_id,
            ];
        }
        return $employee_info;
    }

    private function getDepartmentInfo($announcement)
    {
        $department_info = [];
        $department_ids = $this->getDecodedTarget($announcement);
        foreach ($department_ids as $department_id) {
            $department = BusinessDepartment::find($department_id);
            $department_info [] = [
                'department_id' => $department->id,
                'name' => $department->name,
            ];
        }
        return $department_info;
    }

    private function getEmployeeTypeInfo($announcement)
    {
        $employee_type_info = [];
        $employee_types = $this->getDecodedTarget($announcement);

        foreach ($employee_types as $employee_type) {
            $employee_type_info [] = [
                'type' => $employee_type,
                'name' => ucwords(str_replace("_", " ", $employee_type), " "),
            ];
        }
        return $employee_type_info;
    }

    private function getStatus($announcement)
    {
        $today_date = Carbon::now();
        $end_date_format = Carbon::parse($announcement->end_date->toDateString() . ' ' . $announcement->end_time);

        if ($end_date_format->lessThan($today_date)) return "Expired";
        if ($announcement->status == self::PUBLISHED) return "Published";
        if ($announcement->status == self::SCHEDULED) return "Scheduled";
    }
}