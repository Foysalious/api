<?php

namespace Database\Factories;

use App\Models\PosOrder;

class PosOrderFactory extends Factory
{
    protected $model = PosOrder::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'partner_wise_order_id' => '1',
            'partner_id'            => '1',
            'customer_id'           => '1',
            'payment_status'        => 'Due',
            'delivery_charge'       => '50',
            'delivery_vendor_name'  => '1',
            'delivery_request_id'   => '1',
            'delivery_thana'        => 'Ramna',
            'delivery_district'     => 'Dhaka',
            'delivery_status'       => 'Created',
            'address'               => $this->faker->address,
            'status'                => 'Pending',
            'sales_channel'         => 'pos',
        ]);
    }
}


