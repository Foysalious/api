<?php

namespace Database\Factories;

use App\Models\PosCustomer;

class PosCustomerFactory extends Factory
{
    protected $model = PosCustomer::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'profile_id' => '1',
        ]);
    }
}
