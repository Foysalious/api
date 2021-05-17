<?php namespace Factory;


use App\Models\PosOrder;

class PosOrderFactory extends Factory
{

    protected function getModelClass()
    {
        return PosOrder::class;// TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'partner_wise_order_id' =>1,
            'partner_id' =>1,
            'customer_id' =>1,
            'payment_status'=> "Due",
            'delivery_charge'=>60,
            'delivery_vendor_name'=>"Paperfly",
            'delivery_request_id'=>0,
            'delivery_thana'=>"Banari Para",
            'delivery_district'=>"Barisal",
            'delivery_status'=>"Created",
            'address'=>$this->faker->address,
            'status'=>'Pending',
            'sales_channel'=>'webstore',

        ]);// TODO: Implement getData() method.
    }
}
