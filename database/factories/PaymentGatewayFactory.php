<?php

namespace Database\Factories;

use Sheba\Dal\PaymentGateway\Model as PaymentGateway;

class PaymentGatewayFactory extends Factory
{
    protected $model = PaymentGateway::class;

    public function definition(): array
    {
        return [
            'service_type'     => 'App\Models\Customer',
            'method_name'      => 'wallet',
            'name_en'          => 'Sheba Credit',
            'asset_name'       => 'sheba_credit',
            'cash_in_charge'   => '0.0',
            'order'            => '1',
            'discount_message' => '5% discount on Sheba Credit!',
            'status'           => 'Published',
            'created_by'       => '0',
            'created_by_name'  => 'automatic',
            'updated_by'       => '370',
            'updated_by_name'  => 'IT - Kazi Fahd Zakwan',
        ];
    }
}
