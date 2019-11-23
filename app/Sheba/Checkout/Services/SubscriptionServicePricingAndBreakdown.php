<?php namespace Sheba\Checkout\Services;


class SubscriptionServicePricingAndBreakdown extends ServicePricingAndBreakdown
{
    protected $totalQuantity = 0;

    public function setTotalQuantity($qty)
    {
        $this->totalQuantity = $qty;
        return $this;
    }

    public function toArray()
    {
        return parent::toArray() + [
            'total_quantity' => $this->totalQuantity
        ];
    }
}
