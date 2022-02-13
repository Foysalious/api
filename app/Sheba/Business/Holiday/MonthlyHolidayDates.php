<?php namespace App\Sheba\Business\Holiday;

use Carbon\Carbon;

class MonthlyHolidayDates
{
    private $timeFrame;
    private $businessHolidays;

    /**
     * @param $time_frame
     * @return $this
     */
    public function setTimeFrame($time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    /**
     * @param $holidays
     * @return $this
     */
    public function setBusinessHolidays($holidays)
    {
        $this->businessHolidays = $holidays;
        return $this;
    }

    /**
     * @return array
     */
    public function getHolidays()
    {
        $dates = [];
        $month_start = $this->timeFrame->start;
        $month_end = $this->timeFrame->end;

        foreach ($this->businessHolidays as $holiday) {
            $start_date = Carbon::parse($holiday->start_date);
            $end_date = Carbon::parse($holiday->end_date);
            for ($d = $start_date; $d->lte($end_date); $d->addDay()) {
                if ($start_date->between($month_start, $month_end)) {
                    $dates[] = $d->format('Y-m-d');
                }
            }
        }

        return $dates;
    }
}