<?php namespace App\Sheba\Business\PayrollComponent\Components\Deductions;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Attendance\AttendanceBasicInfo;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sheba\Dal\Attendance\Contract as AttendanceRepositoryInterface;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessOffice\Type as WorkingDaysType;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\OfficePolicy\Type;
use Sheba\Helpers\TimeFrame;

class BusinessMemberPolicyRulesCalculator
{
    use AttendanceBasicInfo;

    /*** @var Business */
    private $business;
    /*** @var BusinessMember */
    private $businessMember;
    private $payrollSetting;
    private $businessPayDay;
    private $timeFrame;
    private $attendanceRepositoryInterface;
    private $businessHolidayRepo;
    private $businessWeekendRepo;
    /*** @var PolicyActionTaker */
    private $policyActionTaker;
    private $additionBreakdown;

    public function __construct()
    {
        $this->timeFrame = app(TimeFrame::class);
        $this->attendanceRepositoryInterface = app(AttendanceRepositoryInterface::class);
        $this->businessHolidayRepo = app(BusinessHolidayRepoInterface::class);
        $this->businessWeekendRepo = app(BusinessWeekendRepoInterface::class);
        $this->policyActionTaker = new PolicyActionTaker();
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        $this->payrollSetting = $this->business->payrollSetting;
        $this->businessPayDay = $this->payrollSetting->pay_day;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setAdditionBreakdown($addition_breakdown)
    {
        $this->additionBreakdown = $addition_breakdown;
        return $this;
    }

    public function calculate()
    {
        $business_office = $this->business->officeHour;
        $office_start_time = Carbon::parse($business_office->start_time);
        $office_end_time = Carbon::parse($business_office->end_time);
        $start_grace_time = $business_office->start_grace_time;
        $end_grace_time = $business_office->end_grace_time;
        $office_start_time_with_grace = $office_start_time->addMinutes(intval($start_grace_time))->format('H:i:s');
        $office_end_time_with_grace = $office_end_time->subMinutes(intval($end_grace_time))->format('H:i:s');
        $is_grace_period_policy_enable = $business_office->is_grace_period_policy_enable;
        $is_late_checkin_early_checkout_policy_enable = $business_office->is_late_checkin_early_checkout_policy_enable;
        $is_for_late_checkin = $business_office->is_for_late_checkin;
        $is_for_early_checkout = $business_office->is_for_early_checkout;
        $is_unpaid_leave_policy_enable = $business_office->is_unpaid_leave_policy_enable;
        $working_days_type = $business_office->type;
        $is_weekend_included = $business_office->is_weekend_included;
        $number_of_days = $business_office->number_of_days;

        if (!$is_grace_period_policy_enable && !$is_late_checkin_early_checkout_policy_enable && !$is_unpaid_leave_policy_enable) return ['attendance_adjustment' => 0, 'leave_adjustment' => 0, 'tax' => 0];
        $next_pay_day = $this->payrollSetting->next_pay_day;
        $start_date = Carbon::now()->subMonth()->format('Y-m-d');
        $end_date = Carbon::now()->subDay()->format('Y-m-d');
        $time_frame = $this->timeFrame->forDateRange($start_date, $end_date);
        $attendances = $this->attendanceRepositoryInterface->getAllAttendanceByBusinessMemberFilteredWithYearMonth($this->businessMember, $time_frame);
        $business_holiday = $this->businessHolidayRepo->getAllByBusiness($this->business);
        $business_weekend = $this->businessWeekendRepo->getAllByBusiness($this->business)->pluck('weekday_name')->toArray();
        $business_member_leave = $this->businessMember->leaves()->accepted()->between($time_frame)->get();
        list($leaves, $leaves_date_with_half_and_full_day) = $this->formatLeaveAsDateArray($business_member_leave);
        $period = CarbonPeriod::create($time_frame->start, $time_frame->end);
        $dates_of_holidays_formatted = $this->getHolidaysFormatted($business_holiday);
        $total_late_checkin = 0;
        $total_early_checkout = 0;
        $total_late_checkin_or_early_checkout = 0;
        $total_present = 0;
        $total_working_days = 0;
        $grace_time_over = 0;
        $total_policy_working_days = $period->count();
        $weekend_or_holiday_count = $weekend_count = 0;
        $business_member_attendance = $this->getBusinessMemberAttendanceTime($attendances, $business_office);
        foreach ($period as $date) {
            $is_weekend_or_holiday = $this->isWeekendHoliday($date, $business_weekend, $dates_of_holidays_formatted);
            $weekend_or_holiday_count = $is_weekend_or_holiday ? ($weekend_or_holiday_count + 1) : $weekend_or_holiday_count;
            $is_weekend = $this->isWeekend($date, $business_weekend);
            $weekend_count = $is_weekend ? ($weekend_count + 1) : $weekend_count;
            $is_on_leave = $this->isLeave($date, $leaves);
            if (!$is_weekend_or_holiday && !$is_on_leave) {
                $total_working_days++;
                if (array_key_exists($date->format('Y-m-d'), $business_member_attendance)) {
                    if ($business_member_attendance[$date->format('Y-m-d')]['checkin_time'] > $office_start_time->format('H:i:s') || $business_member_attendance[$date->format('Y-m-d')]['checkout_time'] < $office_end_time->format('H:i:s')) $total_late_checkin_or_early_checkout++;
                    if ($business_member_attendance[$date->format('Y-m-d')]['checkin_time'] > $office_start_time->format('H:i:s')) $total_late_checkin++;
                    if ($business_member_attendance[$date->format('Y-m-d')]['checkout_time'] < $office_end_time->format('H:i:s')) $total_early_checkout++;
                    if ($business_member_attendance[$date->format('Y-m-d')]['checkin_time'] > $office_start_time_with_grace || $business_member_attendance[$date->format('Y-m-d')]['checkout_time'] < $office_end_time_with_grace) $grace_time_over++;
                    $total_present++;
                }
            }
        }
        $late_checkin_early_checkout_days = 0;
        if ($is_for_late_checkin && $is_for_early_checkout) $late_checkin_early_checkout_days = $total_late_checkin_or_early_checkout;
        elseif ($is_for_late_checkin && !$is_for_early_checkout) $late_checkin_early_checkout_days = $total_late_checkin;
        elseif (!$is_for_late_checkin && $is_for_early_checkout) $late_checkin_early_checkout_days = $total_early_checkout;
        if ($working_days_type === WorkingDaysType::FIXED) $total_policy_working_days = $number_of_days;
        elseif ($working_days_type === WorkingDaysType::AS_PER_CALENDAR && !$is_weekend_included) $total_policy_working_days = ($total_policy_working_days - $weekend_count);
        $total_absent = ($total_working_days - $total_present);
        $attendance_adjustment = 0;
        $leave_adjustment = 0;
        $this->policyActionTaker->setBusiness($this->business)->setBusinessMember($this->businessMember)->setPayrollSetting($this->payrollSetting)->setAdditionBreakdown($this->additionBreakdown)->setTotalWorkingDays($total_policy_working_days);
        if ($is_grace_period_policy_enable) $attendance_adjustment += $this->policyActionTaker->setPolicyType(Type::GRACE_PERIOD)->setPenaltyDays($grace_time_over)->takeAction();
        if ($is_late_checkin_early_checkout_policy_enable) $attendance_adjustment += $this->policyActionTaker->setPolicyType(Type::LATE_CHECKIN_EARLY_CHECKOUT)->setPenaltyDays($late_checkin_early_checkout_days)->takeAction();
        $leave_adjustment += $this->policyActionTaker->setPolicyType(Type::UNPAID_LEAVE)->setPenaltyDays($total_absent)->setIsUnpaidLeaveEnable($is_unpaid_leave_policy_enable)->takeAction();
        return ['attendance_adjustment' => floatValFormat($attendance_adjustment), 'leave_adjustment' => floatValFormat($leave_adjustment), 'tax' => 0];
    }

    private function getBusinessMemberAttendanceTime($attendances, $business_office)
    {
        $business_member_attendance = [];
        foreach ($attendances as $attendance) {
            $business_member_attendance[$attendance->date] = [
                'checkin_time' => Carbon::parse($attendance->checkin_time)->format('H:i:s'),
                'checkout_time' => $attendance->checkout_time ? Carbon::parse($attendance->checkout_time)->format('H:i:s') : Carbon::parse($business_office->end_time)->format('H:i:s'),
            ];
        }
        return $business_member_attendance;
    }
}