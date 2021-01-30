<?php


namespace Factory;


use App\Models\Order;

class OrderFactory extends Factory
{

    protected function getModelClass()
    {
       return Order::class; // TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'id'=> 1,
            'customer_id' => 1,
            'partner_id' => 1,
            'payer_id' => 1,
            'payer_type' => 'customer',
            'delivery_address_id' => 1,
            'location_id' => 5,
            'delivery_name' => ' President Common Management Association Oparajita Complex',
            'delivery_mobile' => '+8801718741996',
            'delivery_address' => ' Road#10, Avenue#9, House#1222&1223 Mirpur DOHS, Dhaka.',
            'sales_channel' => 'B2B'
     ]);// TODO: Implement getData() method.
    }
}