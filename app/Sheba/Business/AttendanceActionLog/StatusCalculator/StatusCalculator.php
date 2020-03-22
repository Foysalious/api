<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Sheba\Dal\Attendance\Model as Attendance;
abstract class StatusCalculator
{
    /** @var Attendance $attendance */
    protected $attendance;
    protected $action;

    /**
     * @param Attendance $attendance
     * @return $this
     */
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

    abstract public function calculate();
}
