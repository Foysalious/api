<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;

class BusinessOfficeHourFactory extends Factory
{
    protected $model = BusinessOfficeHour::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'business_id'                                  => 1,
            'type'                                         => 'as_per_calendar',
            'is_weekend_included'                          => 1,
            'is_start_grace_time_enable'                   => 0,
            'start_time'                                   => Carbon::now()->addMinutes(15),
            'is_end_grace_time_enable'                     => 0,
            'end_time'                                     => Carbon::now()->subMinutes(15),
            'is_grace_period_policy_enable'                => 0,
            'is_late_checkin_early_checkout_policy_enable' => 0,
            'is_for_late_checkin'                          => 0,
            'is_for_early_checkout'                        => 0,
            'is_unpaid_leave_policy_enable'                => 0,
            'unauthorised_leave_penalty_component'         => 'gross',
        ]);
    }
}
