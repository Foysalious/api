<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Dal\Leave\Model as LeaveModel;

class LeaveTransformer extends TransformerAbstract
{
    public function transform(LeaveModel $leave)
    {
        return [
            'title' => $leave->title,
            'leave_type' => $leave->leaveType->title,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'total_days' => $leave->total_days,
            'status' => $leave->status,
            'requested_on' => $leave->created_at
        ];
    }
}