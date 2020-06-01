<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Helpers\TimeFrame;

class AttendanceTransformer extends TransformerAbstract
{
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    private $businessHoliday;
    private $businessWeekend;
    private $businessMemberLeave;

    /**
     * AttendanceTransformer constructor.
     * @param TimeFrame $time_frame
     * @param $business_holiday
     * @param $business_weekend
     * @param $business_member_leave
     */
    public function __construct(TimeFrame $time_frame, $business_holiday, $business_weekend, $business_member_leave)
    {
        $this->timeFrame = $time_frame;
        $this->businessHoliday = $business_holiday;
        $this->businessWeekend = $business_weekend;
        $this->businessMemberLeave = $business_member_leave;
    }

    /**
     * @param Attendance $attendances
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
            'on_leave' => 0
        ];
        $daily_breakdown = [];
        foreach ($period as $date) {
            $breakdown_data = [];
            $is_weekend_or_holiday_or_leave = $this->isWeekendHolidayLeave($date, $weekend_day, $dates_of_holidays_formatted, $leaves);

            $breakdown_data['weekend_or_holiday_tag'] = null;
            if ($is_weekend_or_holiday_or_leave) {
                $breakdown_data['weekend_or_holiday_tag'] = $this->isWeekendHolidayLeaveTag($date, $leaves, $dates_of_holidays_formatted);

                $statistics['working_days']--;
                if ($this->isLeave($date, $leaves)) $statistics['on_leave']++;
            }
            $breakdown_data['show_attendance'] = 0;
            $breakdown_data['attendance'] = null;
            $breakdown_data['is_absent'] = 0;

            /** @var Attendance $attendance */
            $attendance = $attendances->where('date', $date->toDateString())->first();
            if ($this->hasAttendanceButNotAbsent($attendance)) {
                $attendance_checkin_action = $attendance->checkinAction();
                $attendance_checkout_action = $attendance->checkoutAction();

                $breakdown_data['show_attendance'] = 1;
                $breakdown_data['attendance'] = [
                    'id' => $attendance->id,
                    'check_in' => $attendance_checkin_action ? [
                        'status'    => $attendance_checkin_action->status,
                        'time'      => $attendance->checkin_time,
                        'is_remote' => $attendance_checkin_action->is_remote ?: 0,
                        'address'   => $attendance_checkin_action->is_remote ? json_decode($attendance_checkin_action->location)->address : null
                    ] : null,
                    'check_out' => $attendance_checkout_action ? [
                        'status'    => $attendance_checkout_action->status,
                        'time'      => $attendance->checkout_time,
                        'is_remote' => $attendance_checkout_action->is_remote ?: 0,
                        'address'   => $attendance_checkout_action->is_remote ? json_decode($attendance_checkout_action->location)->address : null
                    ] : null,
                    'note' => $attendance->hasEarlyCheckout() ? $attendance->checkoutAction()->note : null
                ];

                if ($attendance_checkin_action) $statistics[$attendance_checkin_action->status]++;
                if ($attendance_checkout_action) $statistics[$attendance_checkout_action->status]++;
            }
            if ($this->isAbsent($attendance, $is_weekend_or_holiday_or_leave, $date)) {
                $breakdown_data['is_absent'] = 1;
                $statistics[Statuses::ABSENT]++;
            }

            $daily_breakdown[] = ['date' => $date->toDateString()] + $breakdown_data;
        }

        $remain_days = CarbonPeriod::create($this->timeFrame->end->addDay(), $this->timeFrame->start->endOfMonth());
        foreach ($remain_days as $date) {
            $is_weekend_or_holiday = $this->isWeekendHolidayLeave($date, $weekend_day, $dates_of_holidays_formatted, $leaves);
            if ($is_weekend_or_holiday) {
                $statistics['working_days']--;
                if ($this->isLeave($date, $leaves)) $statistics['on_leave']++;
            }
        }

        return ['statistics' => $statistics, 'daily_breakdown' => $daily_breakdown];
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
     * @param Attendance $attendance
     * @param $is_weekend_or_holiday_or_leave
     * @param Carbon $date
     * @return bool
     */
    private function isAbsent(Attendance $attendance, $is_weekend_or_holiday_or_leave, Carbon $date)
    {
        return ($attendance && $attendance->status == Statuses::ABSENT) || !$attendance && !$is_weekend_or_holiday_or_leave && !$date->eq(Carbon::today());
    }

    /**
     * @param Attendance $attendance
     * @return bool
     */
    private function hasAttendanceButNotAbsent(Attendance $attendance)
    {
        return $attendance && !($attendance->status == Statuses::ABSENT);
    }
}
