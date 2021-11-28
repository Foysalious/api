<?php

namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class VisitHistoryTransformer extends TransformerAbstract
{
    public function transform($own_visits)
    {
        return [
            'id' =>   $own_visits->id,
            'title' => $own_visits->title,
            'status' => $own_visits->status,
            'schedule_date' => $own_visits->schedule_date->toDateTimeString(),
            'timings' => [
                'start_time' => $own_visits->start_date_time ? $own_visits->start_date_time->format('h:i A') : $own_visits->schedule_date->format('h:i A'),
                'end_time' => $own_visits->end_date_time ? $own_visits->end_date_time->format('h:i A') : null,
                'visit_duration' => $own_visits->total_time_in_minutes ? $this->formatMinute($own_visits->total_time_in_minutes) : null
            ],
        ];
    }

    private function formatMinute($minutes)
    {
        $minutes = (int)$minutes;
        $minute = 0;
        if ($minutes < 60) return ".$minutes" . 'h';
        $hour = $minutes / 60;
        $rounded_hour = intval($hour);
        if ($hour > $rounded_hour) $minute = ($minutes - (60 * intval($hour)));
        return $rounded_hour . '.' . $minute . 'h';
    }

}