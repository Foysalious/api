<?php namespace App\Sheba\Business\Leave\Logs;

use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Dal\LeaveStatusChangeLog\Contract as LeaveStatusChangeLogRepo;

class Formatter
{
    /** @var Leave $leave */
    private $leave;
    private $data;
    /** @var LeaveLogRepo $leaveLogRepo */
    private $leaveLogRepo;
    /** @var LeaveStatusChangeLogRepo $leaveStatusChangeLogRepo */
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
        $cancel_log = $this->getLeaveCancelLogDetails() ? $this->getLeaveCancelLogDetails() : [];
        $update_log = $this->getLeaveLogDetails() ? $this->getLeaveLogDetails() : [];
        $this->data = array_merge($cancel_log, $update_log);

        return $this->data;
    }

    private function getLeaveLogDetails()
    {
        $logs = $this->leaveLogRepo->where('leave_id', $this->leave->id)->where('type', '<>', 'leave_adjustment')
            ->select('log', 'created_at')->orderBy('id', 'DESC')->get()->map(function ($log) {
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
