<?php namespace App\Sheba\Business\Attendance\Member;

class StatusPresenter
{
    public static function statuses()
    {
        return [
            'on_time' => 'On Time',
            'late' => 'Late',
            'absent' => 'Absent',
            'left_early' => 'Left Early',
            'Holiday' => 'Holiday',
            'Weekend' => 'Weekend',
            null => null,
        ];
    }
}