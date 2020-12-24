<?php namespace Sheba\Checkout\Services;


class SubscriptionServicePricingAndBreakdown extends ServicePricingAndBreakdown
{
    protected $totalQuantity = 0;

    public function setTotalQuantity($qty)
    {
        $this->totalQuantity = $qty;
        $this->originalPrice = $this->originalPrice * $qty;
        $this->discountedPrice = $this->discountedPrice * $qty;
        $this->discount = $this->discount * $qty;
        return $this;
    }


    public function toArray()
    {
        return parent::toArray() + [
                'total_quantity' => $this->totalQuantity
            ];
    }
}
