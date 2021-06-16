<?php

/**
 * Khairun Nahar
 * pos Customer factory for sDelivery Automation
 * May,2021
 */



namespace Factory;


use App\Models\PosCustomer;

class PosCustomerFactory extends Factory
{

    protected function getModelClass()
    {
        return PosCustomer::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'profile_id' => '1',
        ]);
    }
}