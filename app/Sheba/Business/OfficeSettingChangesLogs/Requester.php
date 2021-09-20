<?php namespace App\Sheba\Business\OfficeSettingChangesLogs;

use App\Models\Business;

class Requester
{
    private $previousAttendanceType;
    private $newAttendanceType;
    /*** @var Business $business*/
    private $business;
    private $businessWeekend;
    private $previousWorkingDaysType;
    private $previousNumberOfDays;
    private $previousIsWeekendIncluded;
    private $request;
    private $newWeekends;
    private $newWorkingDaysType;
    private $newNumberOfDays;
    private $newIsWeekendIncluded;

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function getBusiness()
    {
        return $this->business;
    }

    public function setPreviousAttendanceType($previous_attendance_type)
    {
        $this->previousAttendanceType = $previous_attendance_type;
        return $this;
    }

    public function getPreviousAttendanceType()
    {
        return $this->previousAttendanceType;
    }

    public function setNewAttendanceType($new_attendance_type)
    {
        $this->newAttendanceType = $new_attendance_type;
        return $this;
    }

    public function getNewAttendanceType()
    {
        return $this->newAttendanceType;
    }

    public function setPreviousWeekends($business_weekend)
    {
        $this->businessWeekend = $business_weekend;
        return $this;
    }

    public function getPreviousWeekends()
    {
        return $this->businessWeekend;
    }

    public function setPreviousTotalWorkingDaysType($previous_working_days_type)
    {
        $this->previousWorkingDaysType = $previous_working_days_type;
        return $this;
    }

    public function getPreviousTotalWorkingDaysType()
    {
        return $this->previousWorkingDaysType;
    }

    public function setPreviousNumberOfDays($previous_number_of_days)
    {
        $this->previousNumberOfDays = $previous_number_of_days;
        return $this;
    }

    public function getPreviousNumberOfDays()
    {
        return $this->previousNumberOfDays;
    }

    public function setPreviousIsWeekendIncluded($previous_is_weekend_included)
    {
        $this->previousIsWeekendIncluded = $previous_is_weekend_included;
        return $this;
    }

    public function getPreviousIsWeekendIncluded()
    {
        return $this->previousIsWeekendIncluded;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        $this->newWeekends = json_decode($request->weekends,1);
        $this->newWorkingDaysType = $request->working_days_type;
        $this->newNumberOfDays = $request->days;
        $this->newIsWeekendIncluded = $request->is_weekend_included;
        return $this;
    }

    public function getNewWeekends()
    {
        return $this->newWeekends;
    }

    public function getNewWorkingDaysType()
    {
        return $this->newWorkingDaysType;
    }

    public function getNewNumberOfDays()
    {
        return $this->newNumberOfDays;
    }

    public function getNewIsWeekendIncluded()
    {
        return $this->newIsWeekendIncluded;
    }
}