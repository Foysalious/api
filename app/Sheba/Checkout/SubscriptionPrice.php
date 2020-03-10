<?php namespace Sheba\Checkout;


use App\Models\ServiceSubscription;
use App\Models\ServiceSubscriptionDiscount;
use App\Sheba\Checkout\Discount;

class SubscriptionPrice extends Discount
{
    private $subscriptionType;
    private $scheduleDateQuantity;

    public function setType($type)
    {
        $this->subscriptionType = $type;
        return $this;
    }

    public function setScheduleDateQuantity($quantity)
    {
        $this->scheduleDateQuantity = $quantity;
        return $this;
    }

    protected function calculateServiceDiscount()
    {
        $discount = $this->serviceObject->serviceModel->subscription->getDiscount($this->subscriptionType, $this->scheduleDateQuantity);
        if ($discount) $this->calculateDiscount($discount);
        $this->discounted_price = $this->original_price - $this->discount;
    }

    private function calculateDiscount(ServiceSubscriptionDiscount $discount)
    {
        $this->hasDiscount = 1;
        $this->cap = (double)$discount->cap;
        $this->amount = (double)$discount->discount_amount;
        $this->sheba_contribution = (double)$discount->sheba_contribution;
        $this->partner_contribution = (double)$discount->partner_contribution;
        $this->discount = $discount->getApplicableAmount($this->original_price, $this->quantity);
        if ($discount->isPercentage()) {
            $this->discount_percentage = (double)$discount->discount_amount;
            $this->isDiscountPercentage = 1;
        }
    }
}
