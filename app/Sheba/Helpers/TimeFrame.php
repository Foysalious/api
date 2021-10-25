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

    public function forDateRange($start_date, $end_date)
    {
        $this->start = Carbon::parse($start_date)->hour(0)->minute(0)->second(0);
        $this->end = Carbon::parse($end_date)->hour(23)->minute(59)->second(59);
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

    public function forTodayAndYesterday()
    {
        $date = Carbon::today();
        $this->start = $date->copy()->subDay()->startOfDay();
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

    public function forSixMonth(Carbon $date)
    {
        $this->start = $date->copy()->subMonths(6)->startOfMonth();
        $this->end = $date->copy()->endOfMonth();
        return $this;
    }

    public function forCurrentWeek($week_start = null)
    {
        $this->start = Carbon::now()->startOfWeek($week_start ?: Carbon::SUNDAY);
        $this->end = Carbon::now()->endOfWeek(Carbon::SATURDAY);
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
        $this->start = $date->copy()->startOfWeek($week_start ?: Carbon::SUNDAY);
        $this->end = $date->endOfWeek($week_end ?: Carbon::SATURDAY);
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
        $current_month = $date->month;
        $quarter = (int)(ceil($current_month / 3));
        if ($previous) $quarter -= 1;
        if ($quarter <= 0) {
            $year = $year - 1;
            $quarter = 4;
        }
        $start_month = (($quarter - 1) * 3) + 1;
        $end_month = $start_month + 2;
        $this->start = $date->copy()->month($start_month)->year($year)->startOfMonth();
        $this->end = $date->copy()->month($end_month)->year($year)->endOfMonth();
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

    /**
     * CALCULATING A FISCAL YEAR OF A COMPANY
     * PASS THE START MONTH OF A COMPANY
     *
     * @param Carbon $date
     * @param $start_month
     * @return $this
     */
    public function forAFiscalYear(Carbon $date, $start_month)
    {
        $current_month = $date->month;
        $for_start_calculation = clone $date;
        $for_end_calculation = clone $date;

        if ($date->month >= $start_month) {
            $this->start = $for_start_calculation->addMonths($start_month - $current_month)->startOfMonth();
            $this->end = $for_end_calculation->addYear()->addMonths($start_month - ($current_month + 1))->endOfMonth();
        } else {
            $this->start = $for_start_calculation->subYear()->addMonths($start_month - $current_month)->startOfMonth();
            $this->end = $for_end_calculation->addMonths($start_month - ($current_month + 1))->endOfMonth();
        }

        return $this;
    }

    public function toDateString($separator = " - "): string
    {
        return $this->start->toDateString() . $separator . $this->end->toDateString();
    }
}
