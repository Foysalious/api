<?php

namespace Database\Factories;

use App\Models\Order;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'payer_id'      => 1,
            'payer_type'    => 'customer',
            'sales_channel' => 'Web',
        ]);
    }
}
