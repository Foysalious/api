<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class HolidayListTransformer extends TransformerAbstract
{
    private $firstDayOfPreviousMonth;
    private $lastDayOfNextMonth;

    /**
     * HolidayListTransformer constructor.
     * @param Carbon $firstDayOfPreviousMonth
     * @param Carbon $lastDayOfNextMonth
     */
    public function __construct(Carbon $firstDayOfPreviousMonth, Carbon $lastDayOfNextMonth)
    {
        $this->firstDayOfPreviousMonth = $firstDayOfPreviousMonth;
        $this->lastDayOfNextMonth = $lastDayOfNextMonth;
    }

    public function transform($holiday)
    {
        $dates = [];
        for ($d = $holiday->start_date; $d->lte($holiday->end_date); $d->addDay()) {
            if ($holiday->start_date->between($this->firstDayOfPreviousMonth, $this->lastDayOfNextMonth)) {
                $dates[] = $d->format('Y-m-d');
            }
        }

        return $dates;
    }
}
