<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Business\Attendance\AttendanceTypes\AttendanceModeType;

abstract class AttendanceType
{
    /** @var AttendanceType */
    protected $next;
    /** @var AttendanceError */
    protected $error;
    /*** @var AttendanceModeType  */
    protected $attendanceModeType;

    public function setNext(AttendanceType $next)
    {
        $this->next = $next;
        return $this;
    }

    public function setError(AttendanceError $error)
    {
        if ($this->next) $this->next->setError($error);
        $this->error = $error;
        return $this;
    }

    public function setAttendanceModeType(AttendanceModeType $attendance_mode_type)
    {
        if ($this->next) $this->next->setAttendanceModeType($attendance_mode_type);
        $this->attendanceModeType = $attendance_mode_type;
        return $this;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getAttendanceModeType()
    {
        return $this->attendanceModeType;
    }

    abstract function check();
}
