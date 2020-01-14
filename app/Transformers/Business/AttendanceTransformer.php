<?php namespace App\Transformers\Business;

use Carbon\CarbonPeriod;
use League\Fractal\TransformerAbstract;
use Sheba\Helpers\TimeFrame;

class AttendanceTransformer extends TransformerAbstract
{
    /** @var TimeFrame $timeFrame */
    private $timeFrame;

    public function __construct(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
    }

    public function transform($attendances)
    {
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
            strtolower($date->format('l'));
            $attendance = $attendances->where('date', $date->toDateString())->first();
            $breakdown_data = [];
            if ($attendance) {
                $breakdown_data = [
                    'check_in'  => $attendance->checkin_time,
                    'check_out' => $attendance->checkout_time,
                    'status'    => $attendance->status,
                    'note'      => null
                ];
            }

            $daily_breakdown[$date->toDateString()] = ['date' => $date->format('M d')] + $breakdown_data;
        }

        return ['statistics' => $statistics, 'daily_breakdown' => $daily_breakdown];
    }
}
