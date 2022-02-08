<?php namespace App\Transformers\Business;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Announcement\Announcement;

class AnnouncementShowTransformer extends TransformerAbstract
{
    const ALL = "all";
    const DEPARTMENT = "department";
    const EMPLOYEE = "employee";

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
            'type' => $announcement->type,
            'is_published' => $announcement->is_published,
            'target' => [
                'type' => $announcement->target_type,
                'type_ids' => $this->targetTypeInfo($announcement),
                'count' => $this->getCount($announcement),
            ],
            'active_between' => 'Dec 02,2020-Dec 05,2020',
            'status' => $this->getStatus($announcement),
            'scheduled_info' => [
                'scheduled_for' => $announcement->scheduled_for,
                'start_date' => $announcement->scheduled_for == self::LATER ? $announcement->start_date : null,
                'start_time' => $announcement->scheduled_for == self::LATER ? $announcement->start_time : null,
                'end_date' => $announcement->end_date->toDateString(),
                'end_time' => $announcement->end_time,
            ],
            'created_by' => $announcement->created_by ?: 'N/S',
            'created_at' => $announcement->created_at->toDateTimeString(),
            'updated_by' => $announcement->updated_by ?: 'N/S',
            'updated_at' => $announcement->updated_at->toDateTimeString(),
        ];
    }

    private function targetTypeInfo($announcement)
    {
        if ($announcement->target_type == self::ALL) return 'all';
        if ($announcement->target_type == self::EMPLOYEE) return $this->getEmployeeInfo($announcement);
        if ($announcement->target_type == self::DEPARTMENT) return $this->getDepartmentInfo($announcement);
    }

    private function getCount($announcement)
    {
        if ($announcement->target_type == self::ALL) return 0;
        if ($announcement->target_type == self::EMPLOYEE) return count($this->getDecodedTarget($announcement));
        if ($announcement->target_type == self::DEPARTMENT) return count($this->getDecodedTarget($announcement));
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

    private function getStatus($announcement)
    {
        $today_date = Carbon::now();
        if ($announcement->end_date->greaterThanOrEqualTo($today_date)) return "Expired";
        if ($announcement->status == self::PUBLISHED) return "Published";
        if ($announcement->status == self::SCHEDULED) return "Scheduled";
    }
}