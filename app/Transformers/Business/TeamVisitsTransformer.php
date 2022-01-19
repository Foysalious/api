<?php

namespace App\Transformers\Business;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use League\Fractal\TransformerAbstract;

class TeamVisitsTransformer extends  TransformerAbstract
{
    public function transform($team_visit)
    {
        $schedule_date = $team_visit->schedule_date;
        /** @var BusinessMember $visitor */
        $visitor = $team_visit->visitor;
        /** @var Member $member */
        $member = $visitor->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        $department = $visitor->department();
        $visit_time_duration_in_hour = $team_visit->total_time_in_minutes ? $this->formatMinute($team_visit->total_time_in_minutes) : null;
        return [
            'id' =>   $team_visit->id,
            'title' => $team_visit->title,
            'status' => $team_visit->status,
            'schedule_date' => $schedule_date->toDateTimeString(),
            'date' => $schedule_date->format('D, F d, Y'),
            'timings' => [
                'start_time' => $team_visit->start_date_time ? $team_visit->start_date_time->format('h:i A') : $team_visit->schedule_date->format('h:i A'),
                'end_time' => $team_visit->end_date_time ? $team_visit->end_date_time->format('h:i A') : null,
                'visit_duration' =>  $team_visit->total_time_in_minutes < 60 ? intval($team_visit->total_time_in_minutes).'m' : $visit_time_duration_in_hour
            ],
            'profile' => [
                'id' => $profile->id,
                'name' => $profile->name ?: null,
                'pro_pic' => $profile->pro_pic,
                'department' => $department ? $department->name : null
            ]
        ];
    }

    /**
     * @param $minutes
     * @return string
     */
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