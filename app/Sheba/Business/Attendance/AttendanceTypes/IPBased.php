<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;

class IPBased implements CheckType
{
    public function check(): string
    {
        return AttendanceTypes::IP_BASED;
    }
}
