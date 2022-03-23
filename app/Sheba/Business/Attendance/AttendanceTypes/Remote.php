<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Business\Attendance\AttendanceTypes\AttendanceSuccess;
use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;

class Remote extends AttendanceType
{
    /**
     * @return AttendanceSuccess | null
     */
    public function check()
    {
        return new AttendanceSuccess(AttendanceTypes::REMOTE);
    }
}
