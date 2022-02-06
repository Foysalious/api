<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\BusinessWeekend\Model as BusinessWeekend;

class BusinessWeekendFactory extends Factory
{
    protected $model = BusinessWeekend::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'weekday_name' => 'Friday',
        ]);
    }
}
