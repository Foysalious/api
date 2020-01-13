<?php namespace App\Transformers\Business\Attendance;

use League\Fractal\TransformerAbstract;

class ReportTransformer extends TransformerAbstract
{
    public function transform()
    {
        $statistics = [
            'working_days'  => 24,
            'on_time'       => 22,
            'late'          => 01,
            'left_early'    => 01,
            'absent'        => 01
        ];
        return [
            'statistics' => $statistics,
            'daily_breakdown' => []
        ];
    }
}
