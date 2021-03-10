<?php namespace Sheba\Business\LeaveRejection;

use Sheba\Dal\LeaveRejectionReason\LeaveRejectionReasonRepository;
use Sheba\Dal\LeaveRejection\LeaveRejectionRepository;
use Sheba\Dal\LeaveRejection\LeaveRejection;
use Sheba\Dal\Leave\Model as Leave;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;

    private $leaveRejectionReasonRepository;
    private $leaveRejectionRepository;
    private $leaveRejectionReasonData;
    private $leaveRejectionData;
    private $reasons;
    private $leave;
    private $note;

    public function __construct(LeaveRejectionRepository $leave_rejection_repository, LeaveRejectionReasonRepository $leave_rejection_reason_repository)
    {
        $this->leaveRejectionRepository = $leave_rejection_repository;
        $this->leaveRejectionReasonRepository = $leave_rejection_reason_repository;
    }

    public function setReasons($reasons)
    {
        $this->reasons = $reasons;
        if ($this->reasons) $this->reasons = json_decode($this->reasons, 1);
        return $this;
    }

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function setLeave(Leave $leave)
    {
        $this->leave = $leave;
        return $this;
    }

    public function create()
    {
        $this->makeLeaveRejectionData();
        DB::transaction(function () {
            $leave_rejection = $this->leaveRejectionRepository->create($this->withBothModificationFields($this->leaveRejectionData));
            $this->reasonCreate($leave_rejection);
        });
        return $this;
    }

    private function makeLeaveRejectionData()
    {
        if ($this->note) $this->leaveRejectionData['note'] = $this->note;
        $this->leaveRejectionData['leave_id'] = $this->leave->id;
        return $this->leaveRejectionData;
    }

    private function reasonCreate(LeaveRejection $leave_rejection)
    {
        $this->makeLeaveRejectionReasonData($leave_rejection);
        $this->leaveRejectionReasonRepository->insert($this->withCreateModificationField($this->leaveRejectionReasonData));
    }

    private function makeLeaveRejectionReasonData(LeaveRejection $leave_rejection)
    {
        foreach ($this->reasons as $reason) {
            $this->leaveRejectionReasonData[] = [
                'leave_rejection_id' => $leave_rejection->id,
                'reason' => $reason
            ];
        }
        return $this->leaveRejectionReasonData;
    }
}