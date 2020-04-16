<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class LeaveListTransformer extends TransformerAbstract
{
    public function transform($leave)
    {
        return [
            'id' => $leave['id'],
            'title' => $leave['title'],
            'leave_type_id' => $leave['leave_type_id'],
            'leave_type' => $leave->leaveType->title,
            'start_date' => $leave['start_date'],
            'end_date' => $leave['end_date'],
            'status' => $leave['status']
        ];
    }
}