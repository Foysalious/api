<?php namespace App\Sheba\Business\OfficeSettingChangesLogs;

use App\Models\Business;
use Carbon\Carbon;

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
    private $newWeekends;
    private $newWorkingDaysType;
    private $newNumberOfDays;
    private $newIsWeekendIncluded;
    private $officeName;
    private $officeIp;
    private $previousOfficeIp;
    private $holidayStartDate;
    private $holidayEndDate;
    private $holidayName;
    private $existingHoliday;
    private $existingHolidayStartDate;
    private $existingHolidayEndDate;
    private $existingHolidayName;
    private $previousOfficeStartTime;
    private $previousOfficeEndTime;
    private $previousIsStartGracePeriodEnable;
    private $previousIsEndGracePeriodEnable;
    private $startGraceTime;
    private $endGraceTime;
    private $officeStartTime;
    private $officeEndTime;
    private $isStartGracePeriodEnable;
    private $isEndGracePeriodEnable;
    private $previousStartGraceTime;
    private $previousEndGraceTime;

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
        $this->newWeekends = json_decode($request->weekends,1);
        $this->newWorkingDaysType = $request->working_days_type;
        $this->newNumberOfDays = $request->days;
        $this->newIsWeekendIncluded = $request->is_weekend_included;
        $this->officeStartTime = $request->start_time;
        $this->officeEndTime = $request->end_time;
        $this->isStartGracePeriodEnable = $request->is_start_grace_period_allow;
        $this->isEndGracePeriodEnable = $request->is_end_grace_period_allow;
        $this->startGraceTime = $request->starting_grace_time;
        $this->endGraceTime = $request->ending_grace_time;
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

    public function setPreviousOfficeIp($previous_office_ip)
    {
        $this->previousOfficeIp = $previous_office_ip;
        return $this;
    }

    public function getPreviousOfficeIp()
    {
        return $this->previousOfficeIp;
    }

    public function setOfficeName($office_name)
    {
        $this->officeName = $office_name;
        return $this;
    }

    public function getOfficeName()
    {
        return $this->officeName;
    }

    public function setOfficeIp($office_ip)
    {
        $this->officeIp = $office_ip;
        return $this;
    }

    public function getOfficeIp()
    {
        return $this->officeIp;
    }

    public function setHolidayStartDate($holiday_start_date)
    {
        $this->holidayStartDate = $holiday_start_date;
        return $this;
    }

    public function getHolidayStartDate()
    {
        return $this->holidayStartDate;
    }

    public function setHolidayEndDate($holiday_end_date)
    {
        $this->holidayEndDate = $holiday_end_date;
        return $this;
    }

    public function getHolidayEndDate()
    {
        return $this->holidayEndDate;
    }

    public function setHolidayName($title)
    {
        $this->holidayName = $title;
        return $this;
    }

    public function getHolidayName()
    {
        return $this->holidayName;
    }

    public function setExistingHoliday($holiday)
    {
        $this->existingHoliday = $holiday;
        return $this;
    }

    public function getExistingHoliday()
    {
        return $this->existingHoliday;
    }

    public function setExistingHolidayStart($existing_holiday_start)
    {
        $this->existingHolidayStartDate = $existing_holiday_start;
        return $this;
    }

    public function setExistingHolidayEnd($existing_holiday_end)
    {
        $this->existingHolidayEndDate = $existing_holiday_end;
        return $this;
    }

    public function setExistingHolidayName($title)
    {
        $this->existingHolidayName = $title;
        return $this;
    }

    public function getExistingHolidayStart()
    {
        return $this->existingHolidayStartDate;
    }

    public function getExistingHolidayEnd()
    {
        return $this->existingHolidayEndDate;
    }

    public function getExistingHolidayName()
    {
        return $this->existingHolidayName;
    }

    public function setPreviousOfficeStartTime($previous_office_start_time)
    {
        $this->previousOfficeStartTime = $previous_office_start_time;
        return $this;
    }

    public function getPreviousOfficeStartTime()
    {
        return $this->previousOfficeStartTime;
    }

    public function setPreviousOfficeEndTime($previous_office_end_time)
    {
        $this->previousOfficeEndTime = $previous_office_end_time;
        return $this;
    }

    public function getPreviousOfficeEndTime()
    {
        return $this->previousOfficeEndTime;
    }

    public function setPreviousIsStartGracePeriodEnable($previous_start_grace_period)
    {
        $this->previousIsStartGracePeriodEnable = $previous_start_grace_period;
        return $this;
    }

    public function getPreviousIsStartGracePeriodEnable()
    {
        return $this->previousIsStartGracePeriodEnable;
    }

    public function setPreviousIsEndGracePeriodEnable($previous_end_grace_period)
    {
        $this->previousIsEndGracePeriodEnable = $previous_end_grace_period;
        return $this;
    }

    public function getPreviousIsEndGracePeriodEnable()
    {
        return $this->previousIsEndGracePeriodEnable;
    }

    public function setPreviousStartGracePeriodTime($start_grace_time)
    {
        $this->previousStartGraceTime = $start_grace_time;
        return $this;
    }

    public function getPreviousStartGracePeriodTime()
    {
        return $this->previousStartGraceTime;
    }

    public function setPreviousEndGracePeriodTime($end_grace_time)
    {
        $this->previousEndGraceTime = $end_grace_time;
        return $this;
    }

    public function getPreviousEndGracePeriodTime()
    {
        return $this->previousEndGraceTime;
    }

    public function getIsStartGracePeriodEnable()
    {
        return $this->isStartGracePeriodEnable;
    }

    public function getIsEndGracePeriodEnable()
    {
        return $this->isEndGracePeriodEnable;
    }

    public function getOfficeStartTime()
    {
        return Carbon::parse($this->officeStartTime)->format('H:i').':59';
    }

    public function getOfficeEndTime()
    {
        return Carbon::parse($this->officeEndTime)->format('H:i').':00';
    }

    public function getStartGraceTime()
    {
        return $this->startGraceTime;
    }

    public function getEndGraceTime()
    {
        return $this->endGraceTime;
    }
}