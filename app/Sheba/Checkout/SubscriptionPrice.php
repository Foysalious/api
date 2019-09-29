<?php namespace Sheba\Checkout;


use App\Models\ServiceSubscription;
use App\Models\ServiceSubscriptionDiscount;
use App\Sheba\Checkout\Discount;

class SubscriptionPrice extends Discount
{
    private $subscriptionType;

    public function setType($type)
    {
        $this->subscriptionType = $type;
        return $this;
    }

    protected function calculateServiceDiscount()
    {
        /** @var  $service_subscription ServiceSubscription */
        $service_subscription = $this->serviceObject->serviceModel->subscription;
        /** @var  $discount ServiceSubscriptionDiscount */
        $discount = $service_subscription->discounts()->where('subscription_type', $this->subscriptionType)->valid()->first();
        if ($discount) {
            $this->hasDiscount = 1;
            $this->cap = (double)$discount->cap;
            $this->amount = (double)$discount->discount_amount;
            $this->sheba_contribution = (double)$discount->sheba_contribution;
            $this->partner_contribution = (double)$discount->partner_contribution;
            if ($discount->isPercentage()) {
                $this->discount_percentage = (double)$discount->discount_amount;
                $this->isDiscountPercentage = 1;
                $this->discount = ($this->original_price * $discount->discount_amount) / 100;
                if ($discount->hasCap() && $this->discount > $discount->cap) $this->discount = $discount->cap;
            } else {
                $this->discount = $this->quantity * $discount->discount_amount;
                if ($this->discount > $this->original_price) $this->discount = $this->original_price;
            }
        }
        $this->discounted_price = $this->original_price - $this->discount;
    }
}