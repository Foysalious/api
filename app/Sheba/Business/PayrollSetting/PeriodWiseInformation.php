<?php namespace App\Sheba\Business\PayrollSetting;

use App\Sheba\Business\Attendance\AttendanceBasicInfo;
use Carbon\Carbon;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;

class PeriodWiseInformation
{
    use AttendanceBasicInfo;

    private $businessHolidayRepo;
    private $businessWeekendRepo;
    private $business;
    private $businessMemberLeave;
    private $isCalculateAttendanceInfo;
    private $periodWiseInformation;

    public function __construct()
    {
        $this->businessHolidayRepo = app(BusinessHolidayRepoInterface::class);
        $this->businessWeekendRepo = app(BusinessWeekendRepoInterface::class);
        $this->periodWiseInformation = collect();
    }

    private $period;
    private $businessOffice;
    private $businessMemberAttendance = [];

    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    public function setBusinessOffice($business_office)
    {
        $this->businessOffice = $business_office;
        $this->business = $this->businessOffice->business;
        return $this;
    }

    public function setBusinessMemberLeave($business_member_leave)
    {
        $this->businessMemberLeave = $business_member_leave;
        return $this;
    }

    public function setAttendance($business_member_attendance)
    {
        $this->businessMemberAttendance = $business_member_attendance;
        return $this;
    }

    public function setIsCalculateAttendanceInfo($is_calculate_attendance_info)
    {
        $this->isCalculateAttendanceInfo = $is_calculate_attendance_info;
        return $this;
    }
    public function get()
    {
        $business_weekend = $this->businessWeekendRepo->getAllByBusiness($this->business)->pluck('weekday_name')->toArray();
        $business_holiday = $this->businessHolidayRepo->getAllByBusiness($this->business);
        $dates_of_holidays_formatted = $this->getHolidaysFormatted($business_holiday);
        $weekend_or_holiday_count = $weekend_count = 0;
        $total_working_days = 0;
        $total_late_checkin_or_early_checkout = 0;
        $total_late_checkin = 0;
        $total_early_checkout = 0;
        $total_present = 0;
        $grace_time_over = 0;
        foreach ($this->period as $date) {
            $is_weekend_or_holiday = $this->isWeekendHoliday($date, $business_weekend, $dates_of_holidays_formatted);
            $is_weekend = $this->isWeekend($date, $business_weekend);
            $weekend_or_holiday_count = $is_weekend_or_holiday ? ($weekend_or_holiday_count + 1) : $weekend_or_holiday_count;
            $weekend_count = $is_weekend ? ($weekend_count + 1) : $weekend_count;
            if ($this->isCalculateAttendanceInfo){
                $office_start_time = Carbon::parse($this->businessOffice->start_time);
                $office_end_time = Carbon::parse($this->businessOffice->end_time);
                $start_grace_time = $this->businessOffice->start_grace_time;
                $end_grace_time = $this->businessOffice->end_grace_time;
                $office_start_time_with_grace = Carbon::parse($this->businessOffice->start_time)->addMinutes(intval($start_grace_time))->format('h:i:s');
                $office_end_time_with_grace = Carbon::parse($this->businessOffice->end_time)->subMinutes(intval($end_grace_time))->format('h:i:s');
                $is_on_leave = $this->isLeave($date, $this->businessMemberLeave);
                if (!$is_weekend_or_holiday && !$is_on_leave) {
                    $total_working_days++;
                    if (array_key_exists($date->format('Y-m-d'), $this->businessMemberAttendance)) {
                        $checkin_time = $this->businessMemberAttendance[$date->format('Y-m-d')]['checkin_time'];
                        $checkout_time = $this->businessMemberAttendance[$date->format('Y-m-d')]['checkout_time'];
                        if ($checkin_time > $office_start_time_with_grace || $checkout_time < $office_end_time_with_grace) $total_late_checkin_or_early_checkout++;
                        if ($checkin_time > $office_start_time_with_grace) $total_late_checkin++;
                        if ($checkout_time < $office_end_time_with_grace) $total_early_checkout++;
                        if ($checkin_time < $office_start_time_with_grace && $checkin_time > $office_start_time || $checkout_time > $office_end_time_with_grace && $checkout_time < $office_end_time ) $grace_time_over++;
                        $total_present++;
                    }
                }
            }
        }
        $this->periodWiseInformation->weekend_or_holiday_count = $weekend_or_holiday_count;
        $this->periodWiseInformation->weekend_count = $weekend_count;
        if ($this->isCalculateAttendanceInfo){
            $this->periodWiseInformation->total_present = $total_present;
            $this->periodWiseInformation->total_working_days = $total_working_days;
            $this->periodWiseInformation->total_late_checkin = $total_late_checkin;
            $this->periodWiseInformation->total_early_checkout = $total_early_checkout;
            $this->periodWiseInformation->total_late_checkin_or_early_checkout = $total_late_checkin_or_early_checkout;
            $this->periodWiseInformation->grace_time_over = $grace_time_over;
        }
        return $this->periodWiseInformation;
    }
}