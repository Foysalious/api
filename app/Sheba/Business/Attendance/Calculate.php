<?php namespace App\Sheba\Business\Attendance;


use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Helpers\TimeFrame;

class Calculate
{
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    private $businessHoliday;
    private $businessWeekend;
    private $forOneEmployee;

    public function __construct(TimeFrame $time_frame, $business_holiday, $business_weekend, $for_one_employee = true)
    {
        $this->timeFrame = $time_frame;
        $this->businessHoliday = $business_holiday;
        $this->businessWeekend = $business_weekend;
        $this->forOneEmployee = $for_one_employee;
    }

    /**
     * @param Attendance $attendances
     * @return array
     */
    public function transform($attendances)
    {
        $weekend_day = $this->businessWeekend->pluck('weekday_name')->toArray();
        $dates_of_holidays_formatted = $this->businessHoliday->map(function ($holiday) {
            return $holiday->start_date->format('Y-m-d');
        })->toArray();

        $period = CarbonPeriod::create($this->timeFrame->start, $this->timeFrame->end);
        $statistics = [
            'working_days' => $this->timeFrame->start->daysInMonth,
            Statuses::ON_TIME => 0,
            Statuses::LATE => 0,
            Statuses::LEFT_EARLY => 0,
            Statuses::ABSENT => 0
        ];
        $daily_breakdown = [];
        foreach ($period as $date) {
            $breakdown_data = [];
            $is_weekend_or_holiday = $this->isWeekend($date, $weekend_day) || $this->isHoliday($date, $dates_of_holidays_formatted) ? 1 : 0;
            $breakdown_data['weekend_or_holiday_tag'] = null;
            if ($is_weekend_or_holiday) {
                if ($this->forOneEmployee) $breakdown_data['weekend_or_holiday_tag'] = $this->isWeekend($date, $weekend_day) ? 'Weekend' : 'Holiday';
                $statistics['working_days']--;
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
                        'checkin_time' => $attendance->checkin_time,
                        'checkout_out' => $attendance->checkout_time,
                        'status' => $is_weekend_or_holiday ? null : $attendance->status,
                        'note' => $attendance->hasEarlyCheckout() ? $attendance->checkoutAction()->note : null
                    ];
                }
                $statistics[$attendance->status]++;
            }
            if (!$attendance && !$is_weekend_or_holiday) {
                if ($this->forOneEmployee) $breakdown_data['is_absent'] = 1;
                $statistics[Statuses::ABSENT]++;
            }

            if ($this->forOneEmployee) $daily_breakdown[] = ['date' => $date->toDateString()] + $breakdown_data;
        }

        $remain_days = CarbonPeriod::create($this->timeFrame->end->addDay(), $this->timeFrame->start->endOfMonth());
        foreach ($remain_days as $date) {
            $is_weekend_or_holiday = $this->isWeekend($date, $weekend_day) || $this->isHoliday($date, $dates_of_holidays_formatted) ? 1 : 0;
            if ($is_weekend_or_holiday) $statistics['working_days']--;
        }

        return $this->forOneEmployee ? ['statistics' => $statistics, 'daily_breakdown' => $daily_breakdown] : ['statistics' => $statistics];
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
}