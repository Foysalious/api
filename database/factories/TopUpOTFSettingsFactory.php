<?php

namespace Database\Factories;

use Sheba\Dal\TopUpOTFSettings\Model as TopUpOTFSettings;

class TopUpOTFSettingsFactory extends Factory
{
    protected $model = TopUpOTFSettings::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'applicable_gateways' => '["ssl","airtel"]',
            'type'                => 'App\Models\Affiliate',
            'agent_commission'    => '5.03',
        ]);
    }
}
