<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Helpers\TimeFrame;

class AttendanceTransformer extends TransformerAbstract
{
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    private $businessHoliday;
    private $businessWeekend;

    public function __construct(TimeFrame $time_frame, $business_holiday, $business_weekend)
    {
        $this->timeFrame = $time_frame;
        $this->businessHoliday = $business_holiday;
        $this->businessWeekend = $business_weekend;
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
            'on_time' => 0,
            'late' => 0,
            'left_early' => 0,
            'absent' => 0
        ];

        $daily_breakdown = [];
        foreach ($period as $date) {
            $breakdown_data = [];
            $is_weekend_or_holiday = $this->isWeekend($date, $weekend_day) || $this->isHoliday($date, $dates_of_holidays_formatted) ? 1 : 0;

            // $breakdown_data['is_weekend_or_holiday'] = $is_weekend_or_holiday;
            $breakdown_data['weekend_or_holiday_tag'] = null;
            if ($is_weekend_or_holiday) {
                $breakdown_data['weekend_or_holiday_tag'] = $this->isWeekend($date, $weekend_day) ? 'Weekend' : 'Holiday';
                $statistics['working_days']--;
            }
            $breakdown_data['show_attendance'] = 0;
            $breakdown_data['attendance'] = null;

            /** @var Attendance $attendance */
            $attendance = $attendances->where('date', $date->toDateString())->first();
            if ($attendance) {
                $breakdown_data['show_attendance'] = 1;
                $breakdown_data['attendance'] = [
                    'id'            => $attendance->id,
                    'checkin_time'  => $attendance->checkin_time,
                    'checkout_out'  => $attendance->checkout_time,
                    'status'        => $is_weekend_or_holiday ? null : $attendance->status,
                    'note'          => $attendance->hasEarlyCheckout() ? $attendance->checkoutAction()->note : null
                ];
                $statistics[$attendance->status]++;
            }

            $daily_breakdown[] = ['date' => $date->toDateString()] + $breakdown_data;
        }

        return ['statistics' => $statistics, 'daily_breakdown' => $daily_breakdown];
    }

    /**
     * @param Carbon $date
     * @param Collection $weekend_day
     * @return bool
     */
    private function isWeekend(Carbon $date, $weekend_day)
    {
        return in_array(strtolower($date->format('l')), $weekend_day);
    }

    /**
     * @param Carbon $date
     * @param Collection $holidays
     * @return bool
     */
    private function isHoliday(Carbon $date, $holidays)
    {
        return in_array($date->format('Y-m-d'), $holidays);
    }
}
