<?php namespace Factory;


use Carbon\Carbon;
use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;

class BusinessHolidayFactory extends Factory
{

    protected function getModelClass()
    {
        // TODO: Implement getModelClass() method.
        return BusinessHoliday::class;

    }

    protected function getData()
    {
        // TODO: Implement getData() method.
        return array_merge($this->commonSeeds, [
            'title' => 'Test Holiday',
            'start_date' => Carbon::parse('2021-02-21'),
            'end_date' => Carbon::parse('2021-02-23')
        ]);
    }
}