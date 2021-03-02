<?php namespace Factory;


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

            'payer_id' => 1,
            'payer_type' => 'customer',
            'sales_channel' => 'Web'
     ]);// TODO: Implement getData() method.
    }
}