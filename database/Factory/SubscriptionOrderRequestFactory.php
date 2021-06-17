<?php namespace Factory;


class SubscriptionOrderRequest extends Factory
{

    protected function getModelClass()
    {
        return SubscriptionOrderRequest::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'subscription_order_id' => 1,
            'status'=>'pending'
        ]);
    }
}
