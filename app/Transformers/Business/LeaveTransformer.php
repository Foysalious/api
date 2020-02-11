<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Dal\Leave\Model as LeaveModel;

class LeaveTransformer extends TransformerAbstract
{
    public function transform(LeaveModel $leave)
    {
        $start_date = new \DateTime($leave->start_date);
        $end_date = new \DateTime($leave->end_date);
        return [
            'title' => $leave->title,
            'leave_type' => $leave->leaveType->title,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'total_days' => $start_date->diff($end_date)->format("%r%a"),
            'status' => $leave->status,
            'requested_on' => $leave->created_at
        ];
    }
}