<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Dal\LeaveType\Model as LeaveType;

class LeaveBalanceDetailsTransformer extends TransformerAbstract
{
    private $leave_types;

    public function __construct($leave_types_data)
    {
        $this->leave_types = $leave_types_data;
    }

    public function transform($business_member)
    {
        $member = $business_member->member;
        $profile = $member->profile;
        $role = $business_member->role;
        $leaves = $business_member->leaves;

        return [
          'employee_name' => $profile->name,
          'employee_id' => $member->id,
          'designation' => $role ? $role->name : null,
          'department' => $role ? $role->businessDepartment->name : null,
          'leave_balance' => $this->calculate($leaves),
          'leaves' => $this->fetchLeaves($leaves),
        ];
    }

    private function calculate($leaves)
    {
        $single_employee_leave_balance = [];
        foreach ($this->leave_types as $leave_type) {
            $total_leave = $leaves->where('leave_type_id', $leave_type['id'])->sum('total_days');
            array_push($single_employee_leave_balance, [
                'title' => $leave_type['title'],
                'total_used_leaves' => (int)$total_leave,
                'allowed_leaves' => $leave_type['total_days']
            ]);
        }

        return $single_employee_leave_balance;
    }

    private function fetchLeaves($leaves)
    {
        $all_leaves = [];
        foreach ($leaves as $leave)
        {
            array_push($all_leaves, [
                'date' => $leave->created_at->format('d/m/Y'),
                'leave_type' => $this->getLeaveTypeTitle($leave),
                'leave_days' =>(int)$leave->total_days,
                'status' => $leave->status
            ]);
        }

        return $all_leaves;
    }

    private function getLeaveTypeTitle($leave)
    {
        $leave_type = LeaveType::findOrFail($leave->leave_type_id);
        return $leave_type->title;
    }
}