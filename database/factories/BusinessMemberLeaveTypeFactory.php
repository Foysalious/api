<?php

namespace Database\Factories;

use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;

class BusinessMemberLeaveTypeFactory extends Factory
{
    protected $model = BusinessMemberLeaveType::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'total_days' => 20,
            'note'       => 'Leave Auto prorated based on employee joining date',
        ]);
    }
}
