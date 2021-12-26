<?php

namespace Database\Factories;

use Sheba\Dal\BusinessOfficeHours\Model;

class BusinessOfficeHoursFactory extends Factory
{
    protected $model = Model::class;

    public function definition(): array
    {
        return [
            'is_weekend_include'                           => '1',
            'is_start_grace_time_enable'                   => '0',
            'start_time'                                   => '09:00:59',
            'is_end_grace_time_enable'                     => '0',
            'end_time'                                     => '18:00:00',
            'is_grace_period_policy_enable'                => '0',
            'is_late_checkin_early_checkout_policy_enable' => '0',
            'is_for_late_checkin'                          => '1',
            'is_for_late_checkout'                         => '1',
            'is_unpaid_leave_policy_enable'                => '0',
            'unauthorised_leave_penalty_component'         => 'gross',
        ];
    }
}
