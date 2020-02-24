<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class HolidayListTransformer extends TransformerAbstract
{
    public function transform($holiday)
    {
        return $this->listAllDatesBetweenTwoDates($holiday->start_date, $holiday->end_date);
    }

    private function listAllDatesBetweenTwoDates($start_date, $end_date)
    {
        $dates = [];
        for($d = $start_date; $d->lte($end_date); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }
        return $dates;
    }
}