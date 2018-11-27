<?php namespace Sheba\Analysis\PartnerPerformance\Calculator;

use Sheba\Analysis\PartnerPerformance\PartnerPerformance;

class Basic extends PartnerPerformance
{
    protected function get()
    {
        $completed = $this->getDataOf('successfully_completed');
        $complain = $this->getDataOf('order_without_complain');
        $timely_accepted = $this->getDataOf('timely_order_taken');
        $timely_processed = $this->getDataOf('timely_job_start');

        return collect([
            'score' => ($completed['rate'] + $complain['rate'] + $timely_accepted['rate'] + $timely_processed['rate']) / 4,
            'performance_summary' => [
                'total_order_taken' => 51,
                'successfully_completed' => $completed['total_order'],
                'order_without_complain' => $complain['total_order'],
                'timely_order_taken' => $timely_accepted['total_order'],
                'timely_job_start' => $timely_processed['total_order']
            ],
            'successfully_completed' => $completed,
            'order_without_complain' => $complain,
            'timely_order_taken' => $timely_accepted,
            'timely_job_start' => $timely_processed
        ]);
    }

    private function getDataOf($of)
    {
        return [
            'total_order' => 24,
            'rate' => 49,
            'last_week_rate' => 34,
            'is_improved' => 1,
            'last_week_rate_difference' => 15,
            'previous_weeks' => $this->getPreviousWeeksData($of)
        ];
    }

    private function getPreviousWeeksData($of)
    {
        return [
            [
                'name' => 'Week 41',
                'date_range' => [
                    'start' => '2018-11-11',
                    'end' => '2018-11-18'
                ],
                'value' => '12',
            ],
            [
                'name' => 'Week 41',
                'date_range' => [
                    'start' => '2018-11-11',
                    'end' => '2018-11-18'
                ],
                'value' => '12',
            ],
            [
                'name' => 'Week 41',
                'date_range' => [
                    'start' => '2018-11-11',
                    'end' => '2018-11-18'
                ],
                'value' => '12',
            ],
            [
                'name' => 'Week 41',
                'date_range' => [
                    'start' => '2018-11-11',
                    'end' => '2018-11-18'
                ],
                'value' => '12',
            ],
            [
                'name' => 'Week 41',
                'date_range' => [
                    'start' => '2018-11-11',
                    'end' => '2018-11-18'
                ],
                'value' => '12',
            ]
        ];
    }
}