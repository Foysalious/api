<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;

class Remote extends AttendanceType
{
    public function check(): string
    {
        return AttendanceTypes::REMOTE;
    }
}
