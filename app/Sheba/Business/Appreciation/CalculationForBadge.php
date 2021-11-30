<?php namespace App\Sheba\Business\Appreciation;

use Sheba\Dal\Attendance\Contract as AttendanceRepoInterface;
use App\Sheba\Business\HolidayOrWeekendOrLeave;
use Sheba\Dal\Attendance\Model as Attendance;
use App\Models\BusinessMember;
use Sheba\Helpers\TimeFrame;
use Carbon\CarbonPeriod;
use App\Models\Business;
use Carbon\Carbon;

class CalculationForBadge
{
    private $attendanceRepo;
    private $businessMember;
    private $timeFrame;
    private $business;
    private $holidayOrWeekendOrHoliday;

    /**
     * @param TimeFrame $time_frame
     * @param AttendanceRepoInterface $attendance_repo
     * @param HolidayOrWeekendOrLeave $holiday_or_weekend_or_holiday
     */
    public function __construct(TimeFrame $time_frame, AttendanceRepoInterface $attendance_repo, HolidayOrWeekendOrLeave $holiday_or_weekend_or_holiday)
    {
        $this->timeFrame = $time_frame;
        $this->attendanceRepo = $attendance_repo;
        $this->holidayOrWeekendOrHoliday = $holiday_or_weekend_or_holiday;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function attendance()
    {
        $this->earlyBirdBadge();
        $this->lateLateefBadge();
    }

    private function earlyBirdBadge()
    {
        $time_frame = $this->getTimeFrameForEarlyBird();
        $attendances = $this->attendanceRepo->getAllAttendanceByBusinessMemberFilteredWithYearMonth($this->businessMember, $time_frame);

        $this->holidayOrWeekendOrHoliday->setBusinessMember($this->businessMember)
            ->setBusiness($this->business)
            ->setTimeFrame($time_frame);

        $weekend_days = $this->holidayOrWeekendOrHoliday->getWeekendDays();
        $dates_of_holidays = $this->holidayOrWeekendOrHoliday->getDatesOfHolidays();
        list($dates_of_leaves, $dates_of_leaves_with_half_and_full_day) = $this->holidayOrWeekendOrHoliday->getDatesOfLeaveWithHalfDayLeavesInfo();

        $early_bird_counter = $this->businessMember->early_bird_counter;
        if ($early_bird_counter >= 12) $early_bird_counter = 0;

        $late_lateef_counter = $this->businessMember->late_lateef_counter;
        if ($late_lateef_counter >= 4) $late_lateef_counter = 0;

        $date = Carbon::now();

        $is_weekend_or_holiday = $this->holidayOrWeekendOrHoliday->isWeekendHoliday($date, $weekend_days, $dates_of_holidays);
        $is_on_leave = $this->holidayOrWeekendOrHoliday->isOnLeave($date, $dates_of_leaves);

        /** @var Attendance $attendance */
        $attendance = $attendances->where('date', $date->toDateString())->first();
        if ($attendance) {
            $attendance_checkin_action = $attendance->checkinAction();
            if (!$is_weekend_or_holiday && $attendance_checkin_action) {
                if ($attendance_checkin_action->status == 'on_time') {
                    $early_bird_counter = $early_bird_counter + 1;
                } else {
                    $early_bird_counter = 0;
                }
            }
            if (!($is_weekend_or_holiday || $is_on_leave) && $attendance_checkin_action) {
                if ($attendance_checkin_action->status == 'late') {
                    $late_lateef_counter = $late_lateef_counter + 1;
                }
            }

        } elseif ($is_on_leave || !$is_weekend_or_holiday) {
            $early_bird_counter = 0;
        }
       
        #if ($early_bird_counter >= 12) create $business_member_badges;
        dd(2121);
    }

    private function lateLateefBadge()
    {
        $time_frame = $this->getTimeFrameForLateLateef();
        $attendances = $this->attendanceRepo->getAllAttendanceByBusinessMemberFilteredWithYearMonth($this->businessMember, $time_frame);

        $this->holidayOrWeekendOrHoliday->setBusinessMember($this->businessMember)
            ->setBusiness($this->business)
            ->setTimeFrame($time_frame);

        $weekend_days = $this->holidayOrWeekendOrHoliday->getWeekendDays();
        $dates_of_holidays = $this->holidayOrWeekendOrHoliday->getDatesOfHolidays();
        list($dates_of_leaves, $dates_of_leaves_with_half_and_full_day) = $this->holidayOrWeekendOrHoliday->getDatesOfLeaveWithHalfDayLeavesInfo();

        $late_lateef_counter = $this->businessMember->late_lateef_counter;
        if ($late_lateef_counter >= 4) $late_lateef_counter = 0;
        $period = CarbonPeriod::create($time_frame->start, $time_frame->end);
        foreach ($period as $date) {
            dd($date);
            if ($date > Carbon::now()) break;
            $is_weekend_or_holiday = $this->holidayOrWeekendOrHoliday->isWeekendHoliday($date, $weekend_days, $dates_of_holidays);
            $is_on_leave = $this->holidayOrWeekendOrHoliday->isOnLeave($date, $dates_of_leaves);

            /** @var Attendance $attendance */
            $attendance = $attendances->where('date', $date->toDateString())->first();
            if ($attendance) {
                $attendance_checkin_action = $attendance->checkinAction();

                if (!($is_weekend_or_holiday || $is_on_leave) && $attendance_checkin_action) {
                    if ($attendance_checkin_action->status == 'late') {
                        $late_lateef_counter = $late_lateef_counter + 1;
                    }
                }
                #if ($late_lateef_counter >= 4) create $business_member_badges;
            }
        }

        dd(2121);
    }

    private function getTimeFrameForLateLateef()
    {
        $start_date = Carbon::now()->toDateString();
        $end_date = Carbon::now()->endOfMonth()->toDateString();
        return $this->timeFrame->forDateRange($start_date, $end_date);
    }

    private function getTimeFrameForEarlyBird()
    {
        $start_date = Carbon::now()->startOfMonth()->toDateString();
        $end_date = Carbon::now()->endOfMonth()->toDateString();
        return $this->timeFrame->forDateRange($start_date, $end_date);
    }
}