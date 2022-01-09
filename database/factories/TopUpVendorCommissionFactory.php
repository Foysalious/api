<?php

namespace Database\Factories;

use App\Models\TopUpVendorCommission;

class TopUpVendorCommissionFactory extends Factory
{
    protected $model = TopUpVendorCommission::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'agent_commission'      => '1.00',
            'ambassador_commission' => '0.20',
            'type'                  => 'App\Models\Affiliate',
        ]);
    }
}
