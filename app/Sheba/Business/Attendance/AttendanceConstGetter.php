<?php namespace App\Sheba\Business\Attendance;


use Sheba\Helpers\ConstGetter;

class AttendanceConstGetter
{
    use ConstGetter;

    Const REMOTE = 'Remote';
    Const PRESENT = 'Present';
    Const ABSENT = 'Absent';
    Const WEEKEND = 'weekend';
    Const LATE = 'late';
    Const ON_TIME = 'on_time';
    Const LEFT_EARLY = 'left_early';
    Const LEFT_TIMELY = 'left_timely';
    Const HOLIDAY = 'holiday';
    Const FULL_DAY = 'full_day';
    Const FIRST_HALF = 'first_half';
    Const SECOND_HALF = 'second_half';
    Const LOCATION_FETCH_ERROR_MESSAGE = 'No location found due to phone’s poor GPS receiver';


}