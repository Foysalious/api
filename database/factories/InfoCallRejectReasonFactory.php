<?php

namespace Database\Factories;

use Sheba\Dal\InfoCallRejectReason\InfoCallRejectReason;

class InfoCallRejectReasonFactory extends Factory
{
    protected $model = InfoCallRejectReason::class;

    public function definition(): array
    {
        return [
            'name'            => 'Customer Unreachable',
            'key'             => 'customer_unreachable',
            'created_at'      => $this->now,
            'updated_at'      => $this->now,
            'created_by_name' => 'IT - Kazi Fahd Zakwan',
            'updated_by_name' => 'IT - Kazi Fahd Zakwan',
            'created_by'      => '1',
            'updated_by'      => '1',
        ];
    }
}
