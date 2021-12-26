<?php

namespace Database\Factories;

use App\Models\Business;

class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'name'        => 'My Company',
            'sub_domain'  => 'my-company',
            'type'        => 'Company',
            'is_verified' => 1,
            'wallet'      => 1000,
        ]);
    }
}
