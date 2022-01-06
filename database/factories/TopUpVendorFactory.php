<?php

namespace Database\Factories;

use App\Models\TopUpVendor;

class TopUpVendorFactory extends Factory
{
    protected $model = TopUpVendor::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'name'             => 'Mock',
            'amount'           => '100000',
            'gateway'          => 'ssl',
            'sheba_commission' => 4.0,
            'is_published'     => 1,
        ]);
    }
}
