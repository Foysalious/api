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
            'title' => 'Independence day',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
        ]);
    }
}
