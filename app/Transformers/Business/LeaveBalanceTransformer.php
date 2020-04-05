<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class LeaveBalanceTransformer extends TransformerAbstract
{
    private $leave_types;

    public function __construct($leave_types_data)
    {
        $this->leave_types = $leave_types_data;
    }

    public function transform($members)
    {
        $employee_wise_leave_balance = [];
        foreach ($members as $member) {
            $business_member = $member->businessMember;
            $leaves = $business_member->leaves;
            $leave_balance_data = $this->calculate($leaves);
            array_push($employee_wise_leave_balance, [
                'id' => $business_member->id,
                'employee_name' => $member->profile->name,
                'leave_balance' => $leave_balance_data
            ]);
        }

        return ['employees_leave_balance' => $employee_wise_leave_balance, 'leave_types' => $this->leave_types];
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
}