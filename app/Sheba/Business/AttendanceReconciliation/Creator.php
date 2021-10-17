<?php namespace App\Sheba\Business\AttendanceReconciliation;


use Sheba\Dal\Attendance\Contract as AttendanceRepo;
use Sheba\Dal\AttendanceActionLog\Contract as AttendanceActionLogsRepo;
use Sheba\Dal\AttendanceOverrideLogs\AttendanceOverrideLogsRepo;

class Creator
{
    /*** @var Requester $reconciliation_requester*/
    private $reconciliationRequester;
    /*** @var AttendanceRepo $attendanceRepo*/
    private $attendanceRepo;
    /*** @var AttendanceActionLogsRepo $attendanceActionLogsRepo*/
    private $attendanceActionLogsRepo;
    /*** @var AttendanceOverrideLogsRepo $attendanceOverrideLogsRepo*/
    private $attendanceOverrideLogsRepo;

    public function __construct()
    {
        $this->attendanceRepo = app(AttendanceRepo::class);
        $this->attendanceActionLogsRepo = app(AttendanceActionLogsRepo::class);
        $this->attendanceOverrideLogsRepo = app(AttendanceOverrideLogsRepo::class);
    }

    public function setRequester(Requester $reconciliation_requester)
    {
        $this->reconciliationRequester = $reconciliation_requester;
        return $this;
    }

    public function create()
    {
        $checkin = $this->reconciliationRequester->getCheckinTime();
        $checkout = $this->reconciliationRequester->getCheckoutTime();

    }

}