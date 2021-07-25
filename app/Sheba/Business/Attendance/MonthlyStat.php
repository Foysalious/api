<?php namespace App\Sheba\Business\Attendance;

use App\Sheba\Business\Attendance\HalfDaySetting\HalfDayType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Helpers\TimeFrame;

class MonthlyStat
{
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    private $businessHoliday;
    private $businessWeekend;
    private $forOneEmployee;
    private $businessMemberLeave;
    private $business;
    private $officeTimeDurationInMinutes;

    /**
     * MonthlyStat constructor.
     * @param TimeFrame $time_frame
     * @param $business_holiday
     * @param $business_weekend
     * @param $business_member_leave
     * @param bool $for_one_employee
     */
    public function __construct(TimeFrame $time_frame, $business, $business_holiday, $business_weekend, $business_member_leave, $for_one_employee = true)
    {
        $this->timeFrame = $time_frame;
        $this->business = $business;
        $this->businessHoliday = $business_holiday;
        $this->businessWeekend = $business_weekend;
        $this->businessMemberLeave = $business_member_leave;
        $this->forOneEmployee = $for_one_employee;
        $this->setOfficeTimeDuration();
    }

    /**
     * @param $attendances
     * @return array
     */
    public function transform($attendances)
    {
        $data = [];
        $weekend_day = $this->businessWeekend->pluck('weekday_name')->toArray();
        list($leaves, $leaves_date_with_half_and_full_day) = $this->formatLeaveAsDateArray();

        foreach ($this->businessHoliday as $holiday) {
            $start_date = Carbon::parse($holiday->start_date);
            $end_date = Carbon::parse($holiday->end_date);
            for ($d = $start_date; $d->lte($end_date); $d->addDay()) {
                $data[] = $d->format('Y-m-d');
            }
        }
        $dates_of_holidays_formatted = $data;
        $period = CarbonPeriod::create($this->timeFrame->start, $this->timeFrame->end);
        $statistics = [
            'working_days' => $this->timeFrame->start->diffInDays($this->timeFrame->end) + 1,
            Statuses::ON_TIME => 0,
            Statuses::LATE => 0,
            Statuses::LEFT_EARLY => 0,
            Statuses::ABSENT => 0,
            Statuses::LEFT_TIMELY => 0,
            'on_leave' => 0,
            'full_day_leave' => 0,
            'half_day_leave' => 0,
            'present' => 0,
            'left_early_note' => 0,
            'total_hours' => 0,
            'overtime_in_minutes' => 0,
            'overtime' => 0,
        ];

        $daily_breakdown = [];
        foreach ($period as $date) {
            $breakdown_data = [
                'date' => null,
                'weekend_or_holiday_tag' => null,
                'show_attendance' => 0,
                'attendance' => null,
                'is_absent' => 0,
            ];

            $is_weekend_or_holiday = $this->isWeekendHoliday($date, $weekend_day, $dates_of_holidays_formatted);
            $is_on_leave = $this->isLeave($date, $leaves);


            if ($is_weekend_or_holiday || $is_on_leave) {
                if ($this->forOneEmployee) $breakdown_data['weekend_or_holiday_tag'] = $this->isWeekendHolidayLeaveTag($date, $leaves_date_with_half_and_full_day, $dates_of_holidays_formatted);
                if (!$this->isHalfDayLeave($date, $leaves_date_with_half_and_full_day))$statistics['working_days']--;
                if ($this->isFullDayLeave($date, $leaves_date_with_half_and_full_day)) $statistics['full_day_leave']++;
                if ($this->isHalfDayLeave($date, $leaves_date_with_half_and_full_day)) $statistics['half_day_leave'] += 0.5;
            }

            /** @var Attendance $attendance */
            $attendance = $attendances->where('date', $date->toDateString())->first();

            if ($attendance) {
                $overtime_in_minutes = $this->calculateOvertimeInMinutes($attendance->staying_time_in_minutes);
                $attendance_checkin_action = $attendance->checkinAction();
                $attendance_checkout_action = $attendance->checkoutAction();
                if ($this->forOneEmployee) {
                    $breakdown_data['show_attendance'] = 1;
                    $breakdown_data['attendance'] = [
                        'id' => $attendance->id,
                        'check_in' => $attendance_checkin_action ? [
                            'status' => $is_weekend_or_holiday || $this->isFullDayLeave($date, $leaves_date_with_half_and_full_day) ? null : $attendance_checkin_action->status,
                            'time' => Carbon::parse($attendance->checkin_time)->format('h:i a'),
                            'is_remote' => $attendance_checkin_action->is_remote ?: 0,
                            'remote_mode' => $attendance_checkin_action->remote_mode ?: null,
                            'address' => $attendance_checkin_action->is_remote && $attendance_checkin_action->location ? json_decode($attendance_checkin_action->location)->address : null
                        ] : null,
                        'check_out' => $attendance_checkout_action ? [
                            'status' => $is_weekend_or_holiday || $this->isFullDayLeave($date, $leaves_date_with_half_and_full_day) ? null : $attendance_checkout_action->status,
                            'time' => Carbon::parse($attendance->checkout_time)->format('h:i a'),
                            'is_remote' => $attendance_checkout_action->is_remote ?: 0,
                            'remote_mode' => $attendance_checkout_action->remote_mode ?: null,
                            'address' => $attendance_checkout_action->is_remote && $attendance_checkout_action->location ? json_decode($attendance_checkout_action->location)->address : null
                        ] : null,
                        'late_note' => (!($is_weekend_or_holiday || $this->isFullDayLeave($date, $leaves_date_with_half_and_full_day)) && $attendance->hasLateCheckin()) ? $attendance->checkinAction()->note : null,
                        'left_early_note' => (!($is_weekend_or_holiday || $this->isFullDayLeave($date, $leaves_date_with_half_and_full_day)) && $attendance->hasEarlyCheckout()) ? $attendance->checkoutAction()->note : null,
                        'active_hours' => $attendance->staying_time_in_minutes ? $this->formatMinute($attendance->staying_time_in_minutes) : null,
                        'overtime_in_minutes' => $attendance->checkout_time ? $overtime_in_minutes : 0,
                        'overtime' => $attendance->checkout_time ? $this->formatMinute($overtime_in_minutes) : null,
                    ];
                }
                if (!($is_weekend_or_holiday || $this->isFullDayLeave($date, $leaves_date_with_half_and_full_day)) && $attendance_checkin_action) $statistics[$attendance_checkin_action->status]++;
                if (!($is_weekend_or_holiday || $this->isFullDayLeave($date, $leaves_date_with_half_and_full_day)) && $attendance_checkout_action) $statistics[$attendance_checkout_action->status]++;
                $statistics['left_early_note'] = (!($is_weekend_or_holiday || $this->isFullDayLeave($date, $leaves_date_with_half_and_full_day)) && $attendance->hasEarlyCheckout()) ? $attendance->checkoutAction()->note : null;
                $statistics['total_hours'] += $attendance->staying_time_in_minutes;
                $statistics['overtime_in_minutes'] += $attendance->checkout_time ? $overtime_in_minutes : 0;
            }

            if ($this->isAbsent($attendance, ($is_weekend_or_holiday || $this->isFullDayLeave($date, $leaves_date_with_half_and_full_day)), $date)) {
                if ($this->forOneEmployee) $breakdown_data['is_absent'] = 1;
                $statistics[Statuses::ABSENT]++;
            }

            if ($this->forOneEmployee) $breakdown_data['date'] = $date->toDateString();
            if ($this->forOneEmployee) $daily_breakdown[] = $breakdown_data;
        }

        $statistics['present'] = $statistics[Statuses::ON_TIME] + $statistics[Statuses::LATE];
        $statistics['on_leave'] = $statistics['full_day_leave'] + $statistics['half_day_leave'];
        $statistics['total_hours'] = $statistics['total_hours'] ? $this->formatMinute($statistics['total_hours']) : 0;
        $statistics['overtime'] = $statistics['overtime_in_minutes'] ? $this->formatMinute($statistics['overtime_in_minutes']) : 0;


        return $this->forOneEmployee ? ['statistics' => $statistics, 'daily_breakdown' => $daily_breakdown] : ['statistics' => $statistics];
    }

    /**
     * @param $minute
     * @return string
     */
    private function formatMinute($minute)
    {
        if ($minute < 60) return "$minute min";
        $hour = $minute / 60;
        $intval_hr = intval($hour);
        $text = "$intval_hr hr ";
        if ($hour > $intval_hr) $text .= ($minute - (60 * intval($hour))) . " min";
        return $text;
    }

    /**
     * @param Carbon $date
     * @param $weekend_day
     * @return bool
     */
    private function isWeekend(Carbon $date, $weekend_day)
    {
        return in_array(strtolower($date->format('l')), $weekend_day);
    }

    /**
     * @param Carbon $date
     * @param $holidays
     * @return bool
     */
    private function isHoliday(Carbon $date, $holidays)
    {
        return in_array($date->format('Y-m-d'), $holidays);
    }

    /**
     * @return array
     */
    private function formatLeaveAsDateArray()
    {
        $business_member_leaves_date = [];
        $business_member_leaves_date_with_half_and_full_day = [];
        $this->businessMemberLeave->each(function ($leave) use (&$business_member_leaves_date, &$business_member_leaves_date_with_half_and_full_day) {
            $leave_period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($leave_period as $date) {
                array_push($business_member_leaves_date, $date->toDateString());
                $business_member_leaves_date_with_half_and_full_day[$date->toDateString()] = [
                    'is_half_day_leave' => $leave->is_half_day,
                    'which_half_day' => $leave->half_day_configuration,
                ];
            }
        });

        return [array_unique($business_member_leaves_date), $business_member_leaves_date_with_half_and_full_day];
    }

    /**
     * @param Carbon $date
     * @param array $leaves
     * @return bool
     */
    private function isLeave(Carbon $date, array $leaves)
    {
        return in_array($date->format('Y-m-d'), $leaves);
    }

    /**
     * @param Carbon $date
     * @param array $leaves_date_with_half_and_full_day
     * @return int
     */
    private function isFullDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
    {
        if (array_key_exists($date->format('Y-m-d'), $leaves_date_with_half_and_full_day)) {
            if ($leaves_date_with_half_and_full_day[$date->format('Y-m-d')]['is_half_day_leave'] == 0) return 1;
        }
        return 0;
    }

    /**
     * @param Carbon $date
     * @param array $leaves_date_with_half_and_full_day
     * @return int
     */
    private function isHalfDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
    {
        if (array_key_exists($date->format('Y-m-d'), $leaves_date_with_half_and_full_day)) {
            if ($leaves_date_with_half_and_full_day[$date->format('Y-m-d')]['is_half_day_leave'] == 1) return 1;
        }
        return 0;
    }

    /**
     * @param Carbon $date
     * @param array $leaves_date_with_half_and_full_day
     * @return string
     */
    private function whichHalfDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
    {
        if (array_key_exists($date->format('Y-m-d'), $leaves_date_with_half_and_full_day)) {
            if ($leaves_date_with_half_and_full_day[$date->format('Y-m-d')]['which_half_day'] == HalfDayType::FIRST_HALF) return HalfDayType::FIRST_HALF;
        }
        return HalfDayType::SECOND_HALF;
    }

    /**
     * @param $date
     * @param $weekend_day
     * @param $dates_of_holidays_formatted
     * @return int
     */
    private function isWeekendHoliday($date, $weekend_day, $dates_of_holidays_formatted)
    {
        return $this->isWeekend($date, $weekend_day)
            || $this->isHoliday($date, $dates_of_holidays_formatted);

    }

    /**
     * @param $date
     * @param $leaves_date_with_half_and_full_day
     * @param $dates_of_holidays_formatted
     * @return string
     */
    private function isWeekendHolidayLeaveTag($date, $leaves_date_with_half_and_full_day, $dates_of_holidays_formatted)
    {
        if ($this->isFullDayLeave($date, $leaves_date_with_half_and_full_day)) return 'full_day';
        if ($this->isHalfDayLeave($date, $leaves_date_with_half_and_full_day)) return $this->whichHalfDayLeave($date, $leaves_date_with_half_and_full_day);
        if ($this->isHoliday($date, $dates_of_holidays_formatted)) return 'holiday';
        return 'weekend';
    }

    /**
     * @param Attendance $attendance | null
     * @param $is_weekend_or_holiday_or_leave
     * @param Carbon $date
     * @return bool
     */
    private function isAbsent($attendance, $is_weekend_or_holiday_or_leave, Carbon $date)
    {
        return !$attendance && !$is_weekend_or_holiday_or_leave && $date->lt(Carbon::today());
    }

    /**
     * @param Attendance $attendance | null
     * @return bool
     */
    private function hasAttendanceButNotAbsent($attendance)
    {
        return $attendance && !($attendance->status == Statuses::ABSENT);
    }

    private function setOfficeTimeDuration()
    {
        $office_hour = $this->business->officeHour;
        $this->officeTimeDurationInMinutes = Carbon::parse($office_hour->start_time)->diffInMinutes(Carbon::parse($office_hour->end_time)) + 1;
    }

    /**
     * @param $staying_time
     * @return int
     */
    private function calculateOvertimeInMinutes($staying_time)
    {
        if ($staying_time > $this->officeTimeDurationInMinutes) {
            return $staying_time - $this->officeTimeDurationInMinutes;
        } else {
            return 0;
        }
    }
}
