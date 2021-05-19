<?php namespace Factory;


use App\Models\PosOrder;

class PosOrderFactory extends Factory
{

    protected function getModelClass()
    {
        return PosOrder::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'partner_wise_order_id'=>'1',
            'partner_id'=>'1',
            'payment_status'=>'Due',
            'delivery_charge'=>'50',
            'address'=>$this->faker->address,
            'status'=>'Pending',
            'sales_channel'=>'pos'
        ]);
    }
}

  
