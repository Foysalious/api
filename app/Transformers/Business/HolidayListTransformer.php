<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class HolidayListTransformer extends TransformerAbstract
{
    private $startDate;
    private $endDate;

    /**
     * @param Carbon $start_Date
     * @param Carbon $end_date
     */
    public function __construct(Carbon $start_Date, Carbon $end_date)
    {
        $this->startDate = $start_Date;
        $this->endDate = $end_date;
    }

    public function transform($holiday)
    {
        $dates = [];
        for ($d = $holiday->start_date; $d->lte($holiday->end_date); $d->addDay()) {
            if ($holiday->start_date->between($this->startDate, $this->endDate)) {
                $dates[] = $d->format('Y-m-d');
            }
        }

        return $dates;
    }
}
