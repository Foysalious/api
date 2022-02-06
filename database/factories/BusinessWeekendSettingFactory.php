<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\BusinessWeekendSettings\BusinessWeekendSettings;

class BusinessWeekendSettingFactory extends Factory
{
    protected $model = BusinessWeekendSettings::class;
    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge($this->commonSeeds, [
            'weekday_name'       => '["friday"]',
            'start_date'         => Carbon::now(),
        ]);
    }
}