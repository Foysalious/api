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
    protected $checkinTime;
    protected $checkoutTime;


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

    public function setCheckinTime($checkin_time)
    {
        $this->checkinTime = $checkin_time;
        return $this;
    }

    public function setCheckoutTime($checkout_time)
    {
        $this->checkoutTime = $checkout_time;
        return $this;
    }

    abstract public function calculate();
}
