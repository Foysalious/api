<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class HolidayListTransformer extends TransformerAbstract
{
    public function transform($holiday)
    {
        return [
            'title' => $holiday->title,
            'start_date' => $holiday->start_date->format('Y-m-d'),
            'end_date' => $holiday->end_date->format('Y-m-d')
        ];
    }
}