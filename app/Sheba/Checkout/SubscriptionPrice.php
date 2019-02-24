<?php

namespace Sheba\Checkout;


use App\Models\ServiceSubscription;
use App\Sheba\Checkout\Discount;

class SubscriptionPrice extends Discount
{
    protected function calculateServiceDiscount()
    {
        /** @var  $service_subscription ServiceSubscription */
        $service_subscription = $this->serviceObject->serviceModel->subscription;
        if ($service_subscription) {
            $this->hasDiscount = 1;
            $this->cap = (double)$service_subscription->cap;
            $this->amount = (double)$service_subscription->discount_amount;
            $this->sheba_contribution = 100;
            $this->partner_contribution = 0;
            if ($service_subscription->isPercentage()) {
                $this->discount_percentage = $service_subscription->discount_amount;
                $this->isDiscountPercentage = 1;
                $this->discount = ($this->original_price * $service_subscription->discount_amount) / 100;
                if ($service_subscription->hasCap() && $this->discount > $service_subscription->cap) $this->discount = $service_subscription->cap;
            } else {
                $this->discount = $this->quantity * $service_subscription->discount_amount;
                if ($this->discount > $this->original_price) $this->discount = $this->original_price;
            }
        }
        $this->discounted_price = $this->original_price - $this->discount;
    }
}