<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Announcement\Announcement;

class AnnouncementListTransformer extends TransformerAbstract
{
    const ALL = "all";
    const DEPARTMENT = "department";
    const EMPLOYEE = "employee";
    const EMPLOYEE_TYPE = "employee_type";

    const PUBLISHED = 'published';
    const SCHEDULED = 'scheduled';
    const EXPIRED = 'expired';

    public function transform(Announcement $announcement)
    {
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'type' => ucfirst($announcement->type),
            'target_type' => $this->targetType($announcement),
            'status' => $this->getStatus($announcement),
            'end_date' => $announcement->end_date->format('M d, Y'),
            'created_at' => $announcement->created_at->format('M d, Y')
        ];
    }

    private function targetType($announcement)
    {
        if ($announcement->target_type == self::ALL) return 'All';
        if ($announcement->target_type == self::EMPLOYEE) return 'Employee';
        if ($announcement->target_type == self::DEPARTMENT) return 'Department';
        if ($announcement->target_type == self::EMPLOYEE_TYPE) return 'Employee Type';
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