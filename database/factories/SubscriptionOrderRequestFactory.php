<?php

namespace Database\Factories;

class SubscriptionOrderRequest extends Factory
{
    protected $model = SubscriptionOrderRequest::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'subscription_order_id' => 1,
            'status'                => 'pending',
        ]);
    }
}
