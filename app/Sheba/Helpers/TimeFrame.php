<?php namespace Sheba\Helpers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeFrame
{
    /** @var Carbon */
    public $start;
    /** @var Carbon */
    public $end;

    public function __construct($start = null, $end = null)
    {
        Carbon::useMicrosecondsFallback(false);
        $this->set($start, $end);
    }

    public function set($start = null, $end = null)
    {
        $this->start = $start;
        $this->end = $end;
        return $this;
    }

    public function getArray()
    {
        return [$this->start, $this->end];
    }

    public function hasDates()
    {
        return !(empty($this->start) || empty($this->end));
    }

    public function forAMonth($month, $year)
    {
        $start_end_date = findStartEndDateOfAMonth($month, $year);
        $this->start = $start_end_date['start_time'];
        $this->end = $start_end_date['end_time'];
        return $this;
    }

    public function forLastMonth(Carbon $date)
    {
        $month = $date->month - 1;
        $year = $date->year;
        if ($month <= 0) {
            $month = 12;
            $year -= $year;
        }
        return $this->forAMonth($month, $year);
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
        Carbon::setWeekEndsAt(Carbon::SATURDAY);

        $this->start = Carbon::now()->startOfWeek();
        $this->end = Carbon::now()->endOfWeek();
        return $this;
    }

    public function forLifeTime()
    {
        $this->start = Carbon::parse(constants('STARTING_YEAR') . '-01-01');
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

    public function forLastWeek(Carbon $date)
    {
        $date = $date->copy()->addDays(-7);
        return $this->forAWeek($date);
    }

    public function forAQuarter(Carbon $date, $previous = false)
    {
        $year = $date->year;
        $currentMonth = $date->month;
        $quarter = (int)(ceil($currentMonth / 3));
        if ($previous) $quarter -= 1;
        if ($quarter <= 0) {
            $year = $year - 1;
            $quarter = 4;
        }
        $startMonth = (($quarter - 1) * 3) + 1;
        $endMonth = $startMonth + 2;
        $this->start = $date->copy()->month($startMonth)->year($year)->startOfMonth();
        $this->end = $date->copy()->month($endMonth)->year($year)->endOfMonth();
        return $this;
    }

    public function forSomeWeekFromNow($week = 1, $week_start = null, $week_end = null)
    {
        if ($week == 0) return $this->forCurrentWeek($week_start);
        else if ($week > 0) $date = Carbon::today()->addWeeks($week);
        else $date = Carbon::today()->subWeeks(abs($week));

        return $this->forAWeek($date);
    }

    public function hasDateBetween(Carbon $date)
    {
        return $date->between($this->start, $this->end);
    }

    /**
     * @param Request $request
     * @return TimeFrame
     */
    public function fromFrequencyRequest(Request $request)
    {
        $time_frame = null;
        switch ($request->frequency) {
            case "day":
                $date = Carbon::parse($request->date);
                $time_frame = $this->forADay($date);
                break;
            case "week":
                $time_frame = $this->forSomeWeekFromNow($request->week);
                break;
            case "month":
                $time_frame = $this->forAMonth($request->month, $request->year);
                break;
            case "year":
                $time_frame = $this->forAYear($request->year);
                break;
            default:
                echo "Invalid time frame";
        }

        return $time_frame;
    }

    public function forTwoDates($start, $end)
    {
        return $this->set(Carbon::parse($start . " 00:00:00"), Carbon::parse($end . " 23:59:59"));
    }
}
