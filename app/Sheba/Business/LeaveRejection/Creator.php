<?php namespace Sheba\Business\LeaveRejection;

use Sheba\Business\LeaveRejection\Requester as LeaveRejectionRequester;
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
    /** @var LeaveRejectionRequester $leaveRejectionRequester */
    private $leaveRejectionRequester;
    private $leaveRejectionData;
    private $leave;

    public function __construct(LeaveRejectionRepository $leave_rejection_repository, LeaveRejectionReasonRepository $leave_rejection_reason_repository)
    {
        $this->leaveRejectionRepository = $leave_rejection_repository;
        $this->leaveRejectionReasonRepository = $leave_rejection_reason_repository;
    }


    public function setLeaveRejectionRequester(LeaveRejectionRequester $leave_rejection_requester)
    {
        $this->leaveRejectionRequester = $leave_rejection_requester;
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
            $leave_rejection = $this->leaveRejectionRepository->create($this->leaveRejectionData);
            $this->reasonCreate($leave_rejection);
        });
        return $this;
    }

    private function makeLeaveRejectionData()
    {
        if ($this->leaveRejectionRequester->getNote()) $this->leaveRejectionData['note'] = $this->leaveRejectionRequester->getNote();
        $this->leaveRejectionData['leave_id'] = $this->leave->id;
        return $this->leaveRejectionData;
    }

    private function reasonCreate(LeaveRejection $leave_rejection)
    {
        $this->makeLeaveRejectionReasonData($leave_rejection);
        $this->leaveRejectionReasonRepository->insert($this->leaveRejectionReasonData);
    }

    private function makeLeaveRejectionReasonData(LeaveRejection $leave_rejection)
    {
        if (count($this->leaveRejectionRequester->getReasons()) > 0)
            foreach ($this->leaveRejectionRequester->getReasons() as $reason) {
                $this->leaveRejectionReasonData[] = [
                    'leave_rejection_id' => $leave_rejection->id,
                    'reason' => $reason
                ];
            }
        return $this->leaveRejectionReasonData;
    }
}