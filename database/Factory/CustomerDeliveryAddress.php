<?php


namespace Factory;


class CustomerDeliveryAddress extends Factory
{

    protected function getModelClass()
    {
        return CustomerDeliveryAddress::class;// TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'id' => 1,
            'customer_id' => 1,
            'address' => ' Road#10, Avenue#9, House#1222&1223 Mirpur DOHS, Dhaka.',
            'created_by' => 17,
            'created_by_name' => 'Fieoze Ahmed',
            'updated_by' => 0,
            'updated_by_name' => null
        ]);// TODO: Implement getData() method.
    }
}