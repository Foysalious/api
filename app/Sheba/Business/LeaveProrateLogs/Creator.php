<?php namespace App\Sheba\Business\LeaveProrateLogs;

use Sheba\Dal\LeaveProrateLog\Contract as LeaveProrateLogRepository;

class Creator
{
    private $prorateTypeArr = [
        'fiscal_year' => 'Fiscal year',
        'leave_policy' => 'Leave Policy',
        'manual' => 'Manual Prorate',
        'leave_type_create' => 'Leave Type Create',
        'leave_type_update' => 'Leave Type Update'
    ];
    private $businessMember;
    private $prorateType;
    private $proratedLeaveDays;
    private $leaveTypeTarget;
    private $leaveType;
    private $previousLeaveTypeTotalDays;
    /*** @var LeaveProrateLogRepository $leaveProrateLogRepository */
    private $leaveProrateLogRepository;

    public function __construct()
    {
        $this->leaveProrateLogRepository = app(LeaveProrateLogRepository::class);
    }

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setProratedType($prorate_type)
    {
        $this->prorateType = $prorate_type;
        return $this;
    }

    public function setProratedLeaveDays($prorated_leave_days)
    {
        $this->proratedLeaveDays = $prorated_leave_days;
        return $this;
    }

    public function setLeaveType($leave_type)
    {
        $this->leaveType = $leave_type;
        return $this;
    }

    public function setLeaveTypeTarget($leave_type_target)
    {
        $this->leaveTypeTarget = $leave_type_target;
        return $this;
    }

    public function setPreviousLeaveTypeTotalDays($previous_leave_type_total_days)
    {
        $this->previousLeaveTypeTotalDays = $previous_leave_type_total_days;
        return $this;
    }

    public function create()
    {
        $this->leaveProrateLogRepository->create($this->makeData());
    }

    private function makeData()
    {
        return [
            'business_member_id' => $this->businessMember->id,
            'prorate_type' => $this->prorateType,
            'leave_type_target' => $this->leaveTypeTarget,
            'leave_type_target_id' => $this->leaveType->id,
            'leave_type_total_days' => $this->previousLeaveTypeTotalDays,
            'prorated_leave_type_total_days' => $this->proratedLeaveDays,
            'log' => $this->leaveType->title . ' ' . $this->proratedLeaveDays . ' days has been prorated according to ' . $this->prorateTypeArr[$this->prorateType]
        ];
    }

}