<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class HolidayListTransformer extends TransformerAbstract
{
    private $firstDayofPreviousMonth;
    private $lastDayofNextMonth;

    public function __construct($firstDayofPreviousMonth,$lastDayofNextMonth)
    {
       $this->firstDayofPreviousMonth = $firstDayofPreviousMonth;
       $this->lastDayofNextMonth = $lastDayofNextMonth;
    }
    public function transform($holiday)
    {
        return $this->listAllDatesBetweenTwoDates($holiday->start_date, $holiday->end_date);
    }

    private function listAllDatesBetweenTwoDates($start_date, $end_date)
    {
        $dates = [];
        for($d = $start_date; $d->lte($end_date); $d->addDay()) {
            if($start_date->between($this->firstDayofPreviousMonth,$this->lastDayofNextMonth))
            {
                $dates[] = $d->format('Y-m-d');
            }
        }
        return $dates;
    }
}