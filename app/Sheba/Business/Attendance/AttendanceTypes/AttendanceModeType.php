<?php namespace Sheba\Business\Attendance\AttendanceTypes;

class AttendanceModeType
{
    private $attendanceModeType;
    private $businessOfficeId;

    public function setAttendanceModeType($attendance_mode_type)
    {
        $this->attendanceModeType = $attendance_mode_type;
        return $this;
    }

    public function setBusinessOffice($business_office_id)
    {
        $this->businessOfficeId = $business_office_id;
        return $this;
    }

    public function get()
    {
        return $this;
    }

}
