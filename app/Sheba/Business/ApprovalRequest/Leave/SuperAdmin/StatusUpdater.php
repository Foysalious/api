<?php namespace Sheba\Business\ApprovalRequest\Leave\SuperAdmin;

use App\Models\BusinessMember;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\Leave\Contract as LeaveRepository;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;

class StatusUpdater
{
    use ModificationFields;

    private $leaveRepository;
    private $leave;
    private $status;
    private $member;
    private $leaveLogRepo;
    private $previousStatus;
    /**@var BusinessMember $businessMember */
    private $businessMember;


    public function __construct(LeaveRepository $leave_repository, LeaveLogRepo $leave_log_repo)
    {
        $this->leaveRepository = $leave_repository;
        $this->leaveLogRepo = $leave_log_repo;
    }

    public function setLeave(Leave $leave)
    {
        $this->leave = $leave;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->member = $business_member->member;
        $this->setModifier($this->member);
        return $this;
    }

    public function updateStatus()
    {
        $this->previousStatus = $this->leave->status;
        $this->leaveRepository->update($this->leave, $this->withUpdateModificationField(['status' => $this->status]));
        $this->createLog();
    }

    private function createLog()
    {
        $data = $this->withCreateModificationField([
            'leave_id' => $this->leave->id,
            'type' => 'status',
            'from' => $this->previousStatus,
            'to' => $this->status,
            'log' => 'Super Admin changed this leave status from ' . $this->formatText($this->previousStatus) . ' to ' . $this->formatText($this->status),
            'is_changed_by_super' => 1,
        ]);

        $this->leaveLogRepo->create($data);
    }

    private function formatText($value)
    {
       if ($value === Status::ACCEPTED) {
           return 'Approved';
       }
       return ucfirst($value);
    }
}