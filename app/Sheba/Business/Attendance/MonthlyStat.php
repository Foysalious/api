<?php namespace App\Sheba\Business\Attendance;


use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Helpers\TimeFrame;

class MonthlyStat
{
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    private $businessHoliday;
    private $businessWeekend;
    private $forOneEmployee;
    private $businessMemberLeave;

    public function __construct(TimeFrame $time_frame, $business_holiday, $business_weekend, $business_member_leave, $for_one_employee = true)
    {
        $this->timeFrame = $time_frame;
        $this->businessHoliday = $business_holiday;
        $this->businessWeekend = $business_weekend;
        $this->businessMemberLeave = $business_member_leave;
        $this->forOneEmployee = $for_one_employee;
    }

    /**
     * @param $attendances
     * @return array
     */
    public function transform($attendances)
    {
        $data = [];
        $weekend_day = $this->businessWeekend->pluck('weekday_name')->toArray();
        $leaves = $this->formatLeaveAsDateArray();

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
            'working_days' => $this->timeFrame->start->daysInMonth,
            Statuses::ON_TIME => 0,
            Statuses::LATE => 0,
            Statuses::LEFT_EARLY => 0,
            Statuses::ABSENT => 0,
            Statuses::LEFT_TIMELY => 0,
            'on_leave' => 0,
            'present' => 0
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
            $is_weekend_or_holiday_or_leave = $this->isWeekendHolidayLeave($date, $weekend_day, $dates_of_holidays_formatted, $leaves);

            if ($is_weekend_or_holiday_or_leave) {
                if ($this->forOneEmployee) $breakdown_data['weekend_or_holiday_tag'] = $this->isWeekendHolidayLeaveTag($date, $leaves, $dates_of_holidays_formatted);
                $statistics['working_days']--;
                if ($this->isLeave($date, $leaves)) $statistics['on_leave']++;
            }

            /** @var Attendance $attendance */
            $attendance = $attendances->where('date', $date->toDateString())->first();
            if ($attendance) {
                $attendance_checkin_action = $attendance->checkinAction();
                $attendance_checkout_action = $attendance->checkoutAction();
                if ($this->forOneEmployee) {
                    $breakdown_data['show_attendance'] = 1;
                    $breakdown_data['attendance'] = [
                        'id' => $attendance->id,
                        'check_in' => $attendance_checkin_action ? [
                            'status' => $is_weekend_or_holiday_or_leave ? null : $attendance_checkin_action->status,
                            'time' => $attendance->checkin_time,
                            'is_remote' => $attendance_checkin_action->is_remote ?: 0,
                            'address'   => $attendance_checkin_action->is_remote ? json_decode($attendance_checkin_action->location)->address : null
                        ] : null,
                        'check_out' => $attendance_checkout_action ? [
                            'status' => $is_weekend_or_holiday_or_leave ? null : $attendance_checkout_action->status,
                            'time' => $attendance->checkout_time,
                            'is_remote' => $attendance_checkout_action->is_remote ?: 0,
                            'address'   => $attendance_checkout_action->is_remote ? json_decode($attendance_checkout_action->location)->address : null
                        ] : null,
                        'note' => (!$is_weekend_or_holiday_or_leave && $attendance->hasEarlyCheckout()) ? $attendance->checkoutAction()->note : null,
                        'active_hours' => $attendance->staying_time_in_minutes ? $this->formatMinute($attendance->staying_time_in_minutes) : null,
                    ];
                }
                if (!$is_weekend_or_holiday_or_leave && $attendance_checkin_action) $statistics[$attendance_checkin_action->status]++;
                if (!$is_weekend_or_holiday_or_leave && $attendance_checkout_action) $statistics[$attendance_checkout_action->status]++;
                if (!$is_weekend_or_holiday_or_leave && $attendance_checkout_action) $statistics['present']++;
            }

            if ($this->isAbsent($attendance, $is_weekend_or_holiday_or_leave, $date)) {
                if ($this->forOneEmployee) $breakdown_data['is_absent'] = 1;
                $statistics[Statuses::ABSENT]++;
            }
            if ($this->forOneEmployee) $breakdown_data['date'] = $date->toDateString();
            if ($this->forOneEmployee) $daily_breakdown[] = $breakdown_data;
        }

        $remain_days = CarbonPeriod::create($this->timeFrame->end->addDay(), $this->timeFrame->start->endOfMonth());
        foreach ($remain_days as $date) {
            $is_weekend_or_holiday = $this->isWeekendHolidayLeave($date, $weekend_day, $dates_of_holidays_formatted, $leaves);
            if ($is_weekend_or_holiday) {
                $statistics['working_days']--;
                if ($this->isLeave($date, $leaves)) $statistics['on_leave']++;
            }
        }
        $statistics['present'] = $statistics[Statuses::ON_TIME] + $statistics[Statuses::LATE];

        return $this->forOneEmployee ? ['statistics' => $statistics, 'daily_breakdown' => $daily_breakdown] : ['statistics' => $statistics];
    }

    /**
     * @param Attendance $attendances
     * @return array
     */
    public function transformV1($attendances)
    {
        $data = [];
        $weekend_day = $this->businessWeekend->pluck('weekday_name')->toArray();
        $leaves = $this->formatLeaveAsDateArray();

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
            'working_days' => $this->timeFrame->start->daysInMonth,
            Statuses::ON_TIME => 0,
            Statuses::LATE => 0,
            Statuses::LEFT_EARLY => 0,
            Statuses::ABSENT => 0,
            'present' => 0,
            'on_leave' => 0
        ];
        $daily_breakdown = [];
        foreach ($period as $date) {
            $breakdown_data = [];
            $is_weekend_or_holiday_or_leave = $this->isWeekendHolidayLeave($date, $weekend_day, $dates_of_holidays_formatted, $leaves);
            $breakdown_data['weekend_or_holiday_tag'] = null;
            if ($is_weekend_or_holiday_or_leave) {
                if ($this->forOneEmployee) $breakdown_data['weekend_or_holiday_tag'] = $this->isWeekendHolidayLeaveTag($date, $leaves, $dates_of_holidays_formatted);
                $statistics['working_days']--;
                if ($this->isLeave($date, $leaves)) $statistics['on_leave']++;
            }
            $breakdown_data['show_attendance'] = 0;
            $breakdown_data['attendance'] = null;
            $breakdown_data['is_absent'] = 0;

            /** @var Attendance $attendance */
            $attendance = $attendances->where('date', $date->toDateString())->first();
            if ($attendance) {
                if ($this->forOneEmployee) {
                    $breakdown_data['show_attendance'] = 1;
                    $breakdown_data['attendance'] = [
                        'id' => $attendance->id,
                        'checkin_time' => Carbon::parse($attendance->date . ' ' . $attendance->checkin_time)->format('g:i a'),
                        'checkout_time' => $attendance->checkout_time ? Carbon::parse($attendance->date . ' ' . $attendance->checkout_time)->format('g:i a') : null,
                        'staying_time_in_minutes' => $attendance->staying_time_in_minutes ? $this->formatMinute($attendance->staying_time_in_minutes) : null,
                        'status' => $is_weekend_or_holiday_or_leave ? null : $attendance->status,
                        'note' => $attendance->hasEarlyCheckout() ? $attendance->checkoutAction()->note : null
                    ];
                }
                $statistics['present']++;
                $statistics[$attendance->status]++;
            }

            if (!$attendance && !$is_weekend_or_holiday_or_leave && !$date->eq(Carbon::today())) {
                if ($this->forOneEmployee) $breakdown_data['is_absent'] = 1;
                $statistics[Statuses::ABSENT]++;
            }

            if ($this->forOneEmployee) $daily_breakdown[] = ['date' => $date->toDateString()] + $breakdown_data;
        }

        $remain_days = CarbonPeriod::create($this->timeFrame->end->addDay(), $this->timeFrame->start->endOfMonth());
        foreach ($remain_days as $date) {
            $is_weekend_or_holiday = $this->isWeekendHolidayLeave($date, $weekend_day, $dates_of_holidays_formatted, $leaves);
            if ($is_weekend_or_holiday) {
                $statistics['working_days']--;
                if ($this->isLeave($date, $leaves)) $statistics['on_leave']++;
            };
        }

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
        $this->businessMemberLeave->each(function ($leave) use (&$business_member_leaves_date) {
            $leave_period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($leave_period as $date) {
                array_push($business_member_leaves_date, $date->toDateString());
            }
        });

        return array_unique($business_member_leaves_date);
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
     * @param $date
     * @param $weekend_day
     * @param $dates_of_holidays_formatted
     * @param $leaves
     * @return int
     */
    private function isWeekendHolidayLeave($date, $weekend_day, $dates_of_holidays_formatted, $leaves)
    {
        return $this->isWeekend($date, $weekend_day)
        || $this->isHoliday($date, $dates_of_holidays_formatted)
        || $this->isLeave($date, $leaves) ? 1 : 0;

    }

    /**
     * @param $date
     * @param $leaves
     * @param $dates_of_holidays_formatted
     * @return string
     */
    private function isWeekendHolidayLeaveTag($date, $leaves, $dates_of_holidays_formatted)
    {
        return $this->isLeave($date, $leaves) ?
            'On Leave' : ($this->isHoliday($date, $dates_of_holidays_formatted) ? 'Holiday' : 'Weekend');
    }

    /**
     * @param Attendance $attendance | null
     * @param $is_weekend_or_holiday_or_leave
     * @param Carbon $date
     * @return bool
     */
    private function isAbsent($attendance, $is_weekend_or_holiday_or_leave, Carbon $date)
    {
        return !$attendance && !$is_weekend_or_holiday_or_leave && !$date->eq(Carbon::today());
    }

    /**
     * @param Attendance $attendance | null
     * @return bool
     */
    private function hasAttendanceButNotAbsent($attendance)
    {
        return $attendance && !($attendance->status == Statuses::ABSENT);
    }
}