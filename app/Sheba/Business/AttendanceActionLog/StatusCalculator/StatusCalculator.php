<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use App\Models\Business;
use Sheba\Dal\Attendance\Model as Attendance;

abstract class StatusCalculator
{
    /** @var Attendance $attendance */
    protected $attendance;
    protected $action;
    /** @var Business $business */
    protected $business;
    protected $whichHalfDay;


    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }
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
    public function setWhichHalfDay($which_half)
    {
        $this->whichHalfDay = $which_half;
        return $this;
    }

    abstract public function calculate();
}
