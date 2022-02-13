<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\GovernmentHolidays\Model as GovernmentHoliday;

class GovernmentHolidayFactory extends Factory
{
    protected $model = GovernmentHoliday::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'end_date'          =>Carbon::now(),
        ]);
    }
}