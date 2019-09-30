<?php namespace Sheba\Subscription\Customer;

use App\Models\SubscriptionOrder;

class OrderStatusChanger
{
    private $subscriptionOrder;

    public function setSubscriptionOrder(SubscriptionOrder $subscription)
    {
        $this->subscriptionOrder = $subscription;
        return $this;
    }

    public function updateStatus($status)
    {
        $this->subscriptionOrder->update(['status' => $status]);
    }
}