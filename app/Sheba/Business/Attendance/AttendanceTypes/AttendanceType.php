<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

abstract class AttendanceType
{
    private $next;

    public function setNext($next)
    {
        $this->next = $next;
        return $this;
    }
    abstract function check();
}
