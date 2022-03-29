<?php namespace Sheba\Business\Attendance\AttendanceTypes;

class AttendanceSuccess
{
    private $attendanceType;
    private $businessOfficeId;

    public function __construct($attendance_type, $business_office_id = null)
    {
        $this->attendanceType = $attendance_type;
        $this->businessOfficeId = $business_office_id;
    }

    /**
     * @return mixed
     */
    public function getAttendanceType()
    {
        return $this->attendanceType;
    }

    /**
     * @return mixed|null
     */
    public function getBusinessOfficeId()
    {
        return $this->businessOfficeId;
    }
}
