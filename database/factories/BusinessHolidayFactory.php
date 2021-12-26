<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;

class BusinessHolidayFactory extends Factory
{
    protected $model = BusinessHoliday::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'title'      => 'Test Holiday',
            'start_date' => Carbon::parse('2021-02-21'),
            'end_date'   => Carbon::parse('2021-02-23'),
        ]);
    }
}
