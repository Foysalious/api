<?php


namespace Factory;


use App\Models\PosOrderPayment;

class PosOrderPaymentFactory extends Factory
{

    protected function getModelClass()
    {
        return PosOrderPayment::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'pos_order_id' => 1,
            'amount' =>'500',
            'transaction_type' =>'Credit',
            'method'=>'cod'
        ]);
    }
}
