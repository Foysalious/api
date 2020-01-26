<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Sheba\Dal\Attendance\Model as Attendance;

abstract class StatusCalculator
{
    /** @var Attendance $attendance */
    protected $attendance;
    protected $action;
    const LAST_CHECKIN_TIME = "9:30:00";
    const LAST_CHECKOUT_TIME = "18:30:00";
    const MINIMUM_HOURS_TO_STAY_IN_OFFICE = 9;

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
