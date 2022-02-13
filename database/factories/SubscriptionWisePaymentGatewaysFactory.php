<?php

namespace Database\Factories;

use Sheba\Dal\SubscriptionWisePaymentGateway\Model as SubscriptionWisePaymentGateway;

class SubscriptionWisePaymentGatewaysFactory extends Factory
{
    protected $model = SubscriptionWisePaymentGateway::class;
    private $topupCharges = [['key' => 'mock', 'name' => 'Mock', 'commission' => 1, 'otf_commission' => 1,]];
    private $gatewayCharge = [
        ['key' => 'nagad', 'name' => 'Nagad', 'fixed_charge' => 3, 'gateway_charge' => 2,],
        ['key' => 'bkash', 'name' => 'bKash', 'fixed_charge' => 3, 'gateway_charge' => 2],
    ];

    public function definition(): array
    {
        return array_merge([
            'package_id'      => 1,
            'gateway_charges' => json_encode($this->gatewayCharge),
            'topup_charges'   => json_encode($this->topupCharges),
            'expired'         => 0,
        ]);
    }
}
