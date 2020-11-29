<?php namespace App\Sheba\Business\Leave\Logs;

use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Dal\LeaveStatusChangeLog\Contract as LeaveStatusChangeLogRepo;

class Formatter
{
    /**
     * @var Leave
     */
    private $leave;
    private $data;
    /**
     * @var LeaveLogRepo
     */
    private $leaveLogRepo;
    /**
     * @var LeaveStatusChangeLogRepo
     */
    private $leaveStatusChangeLogRepo;

    /**
     * Formatter constructor.
     * @param LeaveLogRepo $leave_log_repo
     * @param LeaveStatusChangeLogRepo $leave_status_change_log_repo
     */
    public function __construct(LeaveLogRepo $leave_log_repo, LeaveStatusChangeLogRepo $leave_status_change_log_repo)
    {
        $this->leaveLogRepo = $leave_log_repo;
        $this->leaveStatusChangeLogRepo = $leave_status_change_log_repo;
    }

    public function setLeave($leave)
    {
        $this->leave = $leave;
        return $this;
    }

    public function format()
    {
        return $this->makeData();
    }

    public function makeData()
    {
        $this->data = array_merge($this->getLeaveCancelLogDetails(), $this->getLeaveLogDetails());

        return $this->data;
    }

    private function getLeaveLogDetails()
    {
        $logs = $this->leaveLogRepo->where('leave_id', $this->leave->id)->where('type', '=', 'leave_update')->select('log', 'created_at')->orderBy('id', 'DESC')->get()->map(function ($log) {
            return ['log' => $log->log, 'created_at' => $log->created_at->format('h:i A - d M, Y')];
        })->toArray();
        return $logs ? $logs : null;
    }

    private function getLeaveCancelLogDetails()
    {
        $logs = $this->leaveStatusChangeLogRepo->where('leave_id', $this->leave->id)->select('log', 'created_at')->orderBy('id', 'DESC')->get()->map(function ($log) {
            return ['log' => $log->log, 'created_at' => $log->created_at->format('h:i A - d M, Y')];
        })->toArray();
        return $logs ? $logs : null;
    }

}
