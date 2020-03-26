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
     * @param Attendance $attendances
     * @return array
     */
    public function transform($attendances)
    {
        $weekend_day = $this->businessWeekend->pluck('weekday_name')->toArray();
        $leaves = $this->formatLeaveAsDateArray();
        $dates_of_holidays_formatted = $this->businessHoliday->map(function ($holiday) {
            return $holiday->start_date->format('Y-m-d');
        })->toArray();

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

    private function isLeave(Carbon $date, array $leaves)
    {
        return in_array($date->format('Y-m-d'), $leaves);
    }

    private function isWeekendHolidayLeave($date, $weekend_day, $dates_of_holidays_formatted, $leaves)
    {
        return $this->isWeekend($date, $weekend_day)
                || $this->isHoliday($date, $dates_of_holidays_formatted)
                || $this->isLeave($date, $leaves) ? 1 : 0;

    }

    private function isWeekendHolidayLeaveTag($date, $leaves, $dates_of_holidays_formatted)
    {
        return $this->isLeave($date, $leaves) ?
            'On Leave' : ($this->isHoliday($date, $dates_of_holidays_formatted) ? 'Holiday' : 'Weekend');
    }
}