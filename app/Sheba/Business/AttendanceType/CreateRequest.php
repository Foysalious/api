<?php namespace Sheba\Business\AttendanceType;


class CreateRequest
{
    private $business;
    private $attendanceType;

    /**
     * @return mixed
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * @param mixed $business
     * @return CreateRequest
     */
    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttendanceType()
    {
        return $this->attendanceType;
    }

    /**
     * @param $attendance_type
     * @return CreateRequest
     */
    public function setAttendanceType($attendance_type)
    {
        $this->attendanceType = $attendance_type;
        return $this;
    }
}