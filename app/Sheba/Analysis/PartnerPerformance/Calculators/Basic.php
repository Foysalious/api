<?php namespace Sheba\Analysis\PartnerPerformance\Calculators;

use App\Models\PartnerOrder;
use Sheba\Analysis\PartnerPerformance\PartnerPerformance;
use Sheba\Helpers\TimeFrame;

class Basic extends PartnerPerformance
{
    private $orderTaken;
    private $orderClosed;

    protected function get()
    {
        $this->orderTaken = PartnerOrder::where('partner_id', $this->partner->id)->whereBetween('created_at', $this->timeFrame->getArray())->count();

        $completed = $this->getDataOf('completed');
        $complain = $this->getDataOf('complain');
        $timely_accepted = $this->getDataOf('timely_accepted');
        $timely_processed = $this->getDataOf('timely_processed');

        return collect([
            'score' => ($completed['rate'] + $complain['rate'] + $timely_accepted['rate'] + $timely_processed['rate']) / 4,
            'performance_summary' => [
                'order_received' => $this->orderTaken,
                'completed' => $completed['total'],
                'no_complain' => $complain['total'],
                'timely_accepted' => $timely_accepted['total'],
                'timely_processed' => $timely_processed['total']
            ],
            'completed' => $completed,
            'order_without_complain' => $complain,
            'timely_accepted' => $timely_accepted,
            'timely_processed' => $timely_processed
        ]);
    }

    private function getDataOf($of)
    {
        $func_name = "getDataOf" . pamel_case($of);

        $previous = $this->isCalculatingWeekly() ? $this->getPreviousDataByWeekly($of) : $this->getPreviousDataByMonthly($of);

        $data = $this->$func_name($this->timeFrame);

        $last = end($previous);

        return [
            'total' => $data['value'],
            'rate' => $rate = $data['rate'],
            'last_rate' => $last_rate = $last['rate'],
            'is_improved' => $last_rate < $rate,
            'last_rate_difference' => abs($rate - $last_rate),
            'previous' => $previous
        ];
    }

    private function getDataOfCompleted(TimeFrame $time_frame)
    {
        $order_closed = $this->partner->orders()->closedAtBetween($time_frame)->count();
        $order_created = $this->partner->orders()->createdAtBetween($time_frame)->count();
        return ['value' => $order_closed, 'rate' => $order_created ? ($order_closed / $order_created) : 0];
    }

    private function getDataOfComplain(TimeFrame $time_frame)
    {
        $complain = 1;
        $order_closed = $this->partner->orders()->closedAtBetween($time_frame)->count();
        $without_complain = $order_closed - $complain;
        return ['value' => $without_complain, 'rate' => $order_closed ? ($without_complain / $order_closed) : 0];
    }

    private function getDataOfTimelyAccepted(TimeFrame $time_frame)
    {
        $not_responded = 1;
        $order_closed = $this->partner->orders()->closedAtBetween($time_frame)->count();
        $timely_accepted = $order_closed - $not_responded;
        return ['value' => $timely_accepted, 'rate' => $order_closed ? ($timely_accepted / $order_closed) : 0];
    }

    private function getDataOfTimelyProcessed(TimeFrame $time_frame)
    {
        $schedule_due = 1;
        $order_closed = $this->partner->orders()->closedAtBetween($time_frame)->count();
        $timely_processed = $order_closed - $schedule_due;
        return ['value' => $timely_processed, 'rate' => $order_closed ? ($timely_processed / $order_closed) : 0];
    }

    private function getPreviousDataByWeekly($of)
    {
        $func_name = "getDataOf" . pamel_case($of);

        $calculating_week = $this->timeFrame->start->weekOfYear;
        $week_start_date = $this->timeFrame->start->copy()->subWeeks(self::CALCULATE_PREVIOUS_SLOT);
        $week_end_date = $this->timeFrame->end->copy()->subWeeks(self::CALCULATE_PREVIOUS_SLOT);
        $data = [];
        for ($week = $calculating_week - self::CALCULATE_PREVIOUS_SLOT; $week < $calculating_week; $week++) {
            $data[] = [
                'name' => 'Week ' . $week,
                'date_range' => [
                    'start' => $week_start_date->toDateString(),
                    'end' => $week_end_date->toDateString()
                ],
            ] + $this->$func_name(new TimeFrame($week_start_date, $week_end_date));

            $week_start_date->addWeek();
            $week_end_date->addWeek();
        }
        return $data;
    }

    private function getPreviousDataByMonthly($of)
    {
        $func_name = "getDataOf" . pamel_case($of);

        $calculating_month = $this->timeFrame->start->month;
        $month_start_date = $this->timeFrame->start->copy()->subMonths(self::CALCULATE_PREVIOUS_SLOT);
        $month_end_date = $this->timeFrame->end->copy()->subMonths(self::CALCULATE_PREVIOUS_SLOT);
        $data = [];
        for ($month = $calculating_month - self::CALCULATE_PREVIOUS_SLOT; $month < $calculating_month; $month++) {
            $data[] = [
                'name' => $month_start_date->format('M'),
                'date_range' => [
                    'start' => $month_start_date->toDateString(),
                    'end' => $month_end_date->toDateString()
                ]
            ] + $this->$func_name(new TimeFrame($month_start_date, $month_end_date));
            $month_start_date->addMonth();
            $month_end_date->addMonth();
        }
        return $data;
    }
}