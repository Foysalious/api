<?php

namespace Database\Factories;

use Sheba\Dal\LeaveType\Model as LeaveType;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'title' => 'Test Leave',
            'total_days' => '10',
            'is_half_day_enable' => 0,
        ];
    }
}
