<?php namespace Factory;

use Sheba\Dal\PaymentGateway\Model;

class PaymentGatewayFactory extends Factory
{

    protected function getModelClass()
    {
        return Model::class;
    }

    protected function getData()
    {
        return [
            'service_type' => 'App\Models\Customer',
            'method_name' => 'wallet',
            'name_en' => 'Sheba Credit',
            'asset_name' => 'sheba_credit',
            'cash_in_charge' => '0.0',
            'order' => '1',
            'discount_message' => '5% discount on Sheba Credit!',
            'status' => 'Published'
        ];


    }
}