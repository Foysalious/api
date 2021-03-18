<?php namespace Factory;


use App\Models\Customer;

class CustomerFactory extends Factory
{
    protected function getModelClass()
    {
        return Customer::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'remember_token' => $this->faker->randomLetter,
            'wallet' => '10000',
            'reward_point' => '5000',
            'order_count' => '0',
            'served_order_count' => '0',
            'voucher_order_count' => '0',
        ]);
    }
}