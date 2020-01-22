<?php namespace App\Transformers\Business;

use Carbon\CarbonPeriod;
use League\Fractal\TransformerAbstract;
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

    public function transform($attendances)
    {
        $weekday = $this->businessWeekend->pluck('weekday_name')->toArray();

        $period = CarbonPeriod::create($this->timeFrame->start, $this->timeFrame->end);
        $statistics = [
            'working_days' => 24,
            'on_time' => 22,
            'late' => 01,
            'left_early' => 01,
            'absent' => 01
        ];

        $daily_breakdown = [];
        foreach ($period as $date) {
            $breakdown_data = [];
            $is_weekend_or_holiday = in_array(strtolower($date->format('l')), $weekday) ? 1 : 0;

            // $breakdown_data['is_weekend_or_holiday'] = $is_weekend_or_holiday;
            $breakdown_data['weekend_or_holiday_tag'] = null;
            if ($is_weekend_or_holiday) $breakdown_data['weekend_or_holiday_tag'] = 'Weekend';
            $breakdown_data['show_attendance'] = 0;
            $breakdown_data['attendance'] = null;

            $attendance = $attendances->where('date', $date->toDateString())->first();
            if ($attendance) {
                $breakdown_data['show_attendance'] = 1;
                $breakdown_data['attendance'] = [
                    'id'            => $attendance->id,
                    'checkin_time'  => $attendance->checkin_time,
                    'checkout_out'  => $attendance->checkout_time,
                    'status'        => $is_weekend_or_holiday ? null : $attendance->status,
                    'note'          => ($attendance->id % 3) == 0 ? 'This is a Dummy Logs' : null
                ];
            }

            $daily_breakdown[] = ['date' => $date->toDateString()] + $breakdown_data;
        }

        return ['statistics' => $statistics, 'daily_breakdown' => $daily_breakdown];
    }
}
