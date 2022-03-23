<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Business\Attendance\AttendanceTypes\AttendanceModeType;
use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;

class Remote extends AttendanceType
{
    public function check()
    {
        $attendance_mode_type = new AttendanceModeType();
        $attendance_mode_type->setAttendanceModeType(AttendanceTypes::REMOTE);
    }
}
