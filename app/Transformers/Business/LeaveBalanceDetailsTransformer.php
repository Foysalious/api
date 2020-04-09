<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Sheba\Helpers\TimeFrame;

class LeaveBalanceDetailsTransformer extends TransformerAbstract
{
    private $leave_types;
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    /** @var BusinessMember $businessMember */
    private $businessMember;

    /**
     * LeaveBalanceDetailsTransformer constructor.
     * @param $leave_types
     * @param TimeFrame $time_frame
     */
    public function __construct($leave_types, TimeFrame $time_frame)
    {
        $this->leave_types = $leave_types;
        $this->timeFrame = $time_frame;
    }

    /**
     * @param $business_member
     * @return array
     */
    public function transform($business_member)
    {
        $this->businessMember = $business_member;
        $member = $business_member->member;
        $profile = $member->profile;
        $role = $business_member->role;
        $leaves = $business_member->leaves;

        return [
            'employee_name' => $profile->name, 'employee_id' => $business_member->id, 'designation' => $role ? $role->name : null, 'department' => $role ? $role->businessDepartment->name : null, 'leave_balance' => $this->calculate(), 'leaves' => $this->fetchLeaves($leaves),
        ];
    }

    /**
     * @return array
     */
    private function calculate()
    {
        $single_employee_leave_balance = [];
        foreach ($this->leave_types as $leave_type) {
            array_push($single_employee_leave_balance, [
                'title' => $leave_type['title'],
                'allowed_leaves' => (int)$leave_type['total_days'],
                'used_leaves' => $this->businessMember->getCountOfUsedLeaveDaysByTypeOnAFiscalYear($leave_type['id'])
            ]);
        }

        return $single_employee_leave_balance;
    }

    /**
     * @param $leaves
     * @return array
     */
    private function fetchLeaves($leaves)
    {
        $all_leaves = [];
        foreach ($leaves as $leave) {
            array_push($all_leaves, [
                'id' => $leave->id,
                'date' => $leave->created_at->format('d/m/Y'),
                'leave_type' => $this->getLeaveTypeTitle($leave),
                'leave_days' => (int)$leave->total_days,
                'status' => $leave->status
            ]);
        }

        return $all_leaves;
    }

    /**
     * @param $leave
     * @return mixed
     */
    private function getLeaveTypeTitle($leave)
    {
        $leave_type = LeaveType::withTrashed()->findOrFail($leave->leave_type_id);
        return $leave_type->title;
    }
}
