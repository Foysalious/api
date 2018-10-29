<?php namespace Sheba\Helpers;

use Carbon\Carbon;

class TimeFrame
{
    public $start;
    public $end;

    public function __construct($start = null, $end = null)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function getArray()
    {
        return [$this->start, $this->end];
    }
    
    public function forAMonth($month, $year)
    {
        $start_end_date = findStartEndDateOfAMonth($month, $year);
        $this->start = $start_end_date['start_time'];
        $this->end = $start_end_date['end_time'];
        return $this;
    }

    public function forADay(Carbon $date)
    {
        $this->start = $date->copy()->startOfDay();
        $this->end = $date->endOfDay();
        return $this;
    }

    public function forToday()
    {
        return $this->forADay(Carbon::today());
    }

    public function forYesterday()
    {
        return $this->forADay(Carbon::yesterday());
    }

    public function forAYear($year)
    {
        $start_end_date = findStartEndDateOfAMonth(0, $year);
        $this->start = $start_end_date['start_time'];
        $this->end = $start_end_date['end_time'];
        return $this;
    }

    public function forCurrentWeek($week_start = null)
    {
        Carbon::setWeekStartsAt($week_start ?: Carbon::SUNDAY);
        $this->start = Carbon::now()->startOfWeek();
        $this->end = Carbon::now()->endOfWeek();
        return $this;
    }

    public function forLifeTime()
    {
        $this->start = Carbon::parse(constants('STARTING_YEAR').'-01-01');
        $this->end = Carbon::now()->endOfYear();
        return $this;
    }

    public function forAWeek(Carbon $date, $week_start = null, $week_end = null)
    {
        Carbon::setWeekStartsAt($week_start ?: Carbon::SUNDAY);
        Carbon::setWeekEndsAt($week_end ?: Carbon::SATURDAY);

        $this->start = $date->copy()->startOfWeek();
        $this->end = $date->endOfWeek();
        return $this;
    }
}