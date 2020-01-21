<?php namespace Sheba\Business\AttendanceActionLog;

use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\Attendance\Statuses;

class StatusCalculator
{
    /** @var Attendance $attendance */
    private $attendance;
    private $action;

    public function __construct()
    {
    }

    public function setAttendance(Attendance $attendance)
    {
        $this->attendance = $attendance;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function calculate()
    {
        return Statuses::ON_TIME;
    }
}
