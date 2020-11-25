<?php namespace App\Sheba\Business\LeaveStatusChangeLog;

use App\Models\BusinessMember;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveStatusChangeLog\Contract as LeaveStatusChangeRepoInterface;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;

    private $leaveStatusChangeRepository;
    private $leave;
    private $previousStatus;
    private $status;
    private $data;
    private $member;

    public function __construct(LeaveStatusChangeRepoInterface $leave_status_change_repository)
    {
        $this->leaveStatusChangeRepository = $leave_status_change_repository;
        $this->data = [];
    }

    public function setLeave(Leave $leave)
    {
        $this->leave = $leave;
        return $this;
    }

    public function setPreviousStatus($previous_status)
    {
        $this->previousStatus = $previous_status;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param $member
     * @return $this
     */
    public function setMember($member)
    {
        $this->profile = $member;
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->member = $business_member->member;
        return $this;
    }

    private function makeData()
    {
        $this->setModifier($this->member);
        $this->data['leave_id'] = $this->leave->id;
        $this->data['from_status'] = $this->previousStatus;
        $this->data['to_status'] = $this->status;
        $this->data['log'] = $this->profile ? "$this->profile $this->status this leave" : "Leave status change from $this->previousStatus to $this->status";
        $this->withCreateModificationField($this->data);
    }

    public function create()
    {
        $this->makeData();
        $this->leaveStatusChangeRepository->create($this->data);
    }
}
