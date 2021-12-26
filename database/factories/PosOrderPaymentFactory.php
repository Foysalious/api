<?php

namespace Database\Factories;

use App\Models\PosOrderPayment;

class PosOrderPaymentFactory extends Factory
{
    protected $model = PosOrderPayment::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'pos_order_id'     => 1,
            'amount'           => '500',
            'transaction_type' => 'Credit',
            'method'           => 'cod',
        ]);
    }
}
