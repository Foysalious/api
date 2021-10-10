<?php namespace Factory;


/**
 * Khairun Nahar
 * pos order factory for sDelivery Automation
 * May,2021
 */


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
            'customer_id'=>'1',
            'payment_status'=>'Due',
            'delivery_charge'=>'50',
            'delivery_vendor_name'=>'1',
            'delivery_request_id'=>'1',
            'delivery_thana'=>'Ramna',
            'delivery_district'=>'Dhaka',
            'delivery_status'=>'Created',
            'address'=>$this->faker->address,
            'status'=>'Pending',
            'sales_channel'=>'pos'
        ]);
    }
}


