<?php namespace App\Sheba\Business\LeaveProrateLogs;

use App\Sheba\Business\LeaveProrateLogs\Creator as LeaveProrateLogCreator;
use Sheba\Dal\LeaveType\Contract as LeaveType;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class ManualLeaveProrateLogRequester
{
    const MANUAL_PRORATE = 'manual';
    private $businessMemberIds;
    private $leaveType;
    private $totalDays;
    private $businessMemberRepo;
    private $businessMembers;
    private $leaveTypeRepo;
    private $leaveProrateLogCreator;

    public function __construct()
    {
        $this->businessMemberRepo = app(BusinessMemberRepositoryInterface::class);
        $this->leaveTypeRepo = app(LeaveType::class);
        $this->leaveProrateLogCreator = app(LeaveProrateLogCreator::class);
    }

    public function setBusinessMemberIds(array $business_member_ids)
    {
        $this->businessMemberIds = $business_member_ids;
        return $this;
    }

    public function setLeaveTypeId($leave_type_id)
    {
        $this->leaveType = $this->leaveTypeRepo->find($leave_type_id);
        return $this;
    }

    public function setTotalDays($total_days)
    {
        $this->totalDays = $total_days;
        return $this;
    }

    public function setProratedLeaveDays($total_days)
    {
        $this->totalDays = $total_days;
        return $this;
    }

    public function create()
    {
        if (!$this->leaveType) return false;
        $this->getBusinessMember();
        foreach ($this->businessMemberIds as $business_member_id) {
            if ($business_member = $this->businessMembers->filter(function ($item) use ($business_member_id) {
                return $item->id == $business_member_id;
            })->first()) {
                $this->leaveProrateLogCreator->setBusinessMember($business_member)
                    ->setProratedType(self::MANUAL_PRORATE)
                    ->setProratedLeaveDays($this->totalDays)
                    ->setLeaveType($this->leaveType)
                    ->setPreviousLeaveTypeTotalDays($this->leaveType->total_days)
                    ->setLeaveTypeTarget(get_class($this->leaveType))
                    ->create();
            }
        }
    }

    private function getBusinessMember()
    {
        $this->businessMembers = $this->businessMemberRepo->builder()->whereIn('id', $this->businessMemberIds)->get();
    }

}