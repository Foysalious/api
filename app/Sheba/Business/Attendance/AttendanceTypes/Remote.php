<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;

class Remote implements CheckType
{
    public function check(): string
    {
        return AttendanceTypes::REMOTE;
    }
}
