<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\Status as LeaveStatus;
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
        $this->businessMember = $business_member->load([
            'leaves' => function ($leave) {
                return $leave->with([
                    'leaveType' => function ($leave_type) {
                        return $leave_type->withTrashed();
                    }
                ]);
            }
        ]);
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        /** @var BusinessRole $role */
        $role = $business_member->role;
        /** @var Leave $leaves */
        $leaves = $business_member->leaves;

        return [
            'employee_name' => $profile->name,
            'employee_id' => $business_member->id,
            'designation' => $role ? $role->name : null,
            'department' => $role ? $role->businessDepartment->name : null,
            'leave_balance' => $this->calculate(),
            'leaves' => $this->formatLeaves($leaves)
        ];
    }

    /**
     * @return array
     */
    private function calculate()
    {
        $single_employee_leave_balance = [];
        foreach ($this->leave_types as $leave_type) {
            $used_leave_days = $this->businessMember->getCountOfUsedLeaveDaysByTypeOnAFiscalYear($leave_type['id']);
            array_push($single_employee_leave_balance, [
                'title' => $leave_type['title'],
                'allowed_leaves' => (int)$leave_type['total_days'],
                'used_leaves' => $used_leave_days,
                'is_leave_days_exceeded' => ($used_leave_days > (int)$leave_type['total_days'])
            ]);
        }

        return $single_employee_leave_balance;
    }

    /**
     * @param $leaves
     * @return array
     */
    private function formatLeaves($leaves)
    {
        $requested_business_member_id = request()->business_member->id;
        $all_leaves = [];
        foreach ($leaves as $leave) {
            $get_current_login_user_leave_request = $leave->requests->where('approver_id', $requested_business_member_id)->first();

            array_push($all_leaves, [
                'id'        => $leave->id,
                'date'      => $leave->created_at->format('d/m/Y'),
                'leave_type'=> $leave->leaveType->title,
                'leave_days'=> (int)$leave->total_days,
                'status'    => LeaveStatus::getWithKeys()[strtoupper($leave->status)],
                'request'   => [
                    'has_access' => $get_current_login_user_leave_request ? true : false,
                    'id' => $get_current_login_user_leave_request ? $get_current_login_user_leave_request->id : null
                ]
            ]);
        }

        return $all_leaves;
    }
}
