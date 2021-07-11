<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;

class LeaveListTransformer extends TransformerAbstract
{
    public function transform($leave)
    {
        $leave_type = $leave->leaveType()->withTrashed()->first();
        return [
            'id' => $leave['id'],
            'title' => $leave['title'],
            'leave_type_id' => $leave['leave_type_id'],
            'leave_type' => $leave_type->title,
            'start_date' => $leave['start_date'],
            'end_date' => $leave['end_date'],
            'status' => LeaveStatusPresenter::statuses()[$leave['status']],
            'created_at' => $leave['created_at'],
            'period' => $leave['start_date']->format('M d, Y') == $leave['end_date']->format('M d, Y') ? $leave['start_date']->format('M d') : $leave['start_date']->format('M d') .' - '. $leave['end_date']->format('M d'),
            'total_days' => $leave['total_days'],
        ];
    }
}
