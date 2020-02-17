<?php namespace App\Sheba\Business\LeaveStatusChangeLog;

use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveStatusChangeLog\Contract as LeaveStatusChangeRepoInterface;

class Creator
{
    private $leaveStatusChangeRepository;
    private $leave;
    private $previousStatus;
    private $status;
    private $data;

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

    private function makeData()
    {
        $this->data['leave_id'] = $this->leave->id;
        $this->data['from_status'] = $this->previousStatus;
        $this->data['to_status'] = $this->status;
    }

    public function create()
    {
        $this->makeData();
        $this->leaveStatusChangeRepository->create($this->data);
    }
}