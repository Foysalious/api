<?php namespace Sheba\Checkout\SubscriptionOrderPlace;

use App\Models\SubscriptionOrder;
use Sheba\Checkout\Services\SubscriptionServicePricingAndBreakdown;

class SubscriptionOrderPlaceWithoutPartner extends SubscriptionOrderPlace
{
    /** @var SubscriptionServicePricingAndBreakdown */
    protected $breakdown;

    public function setPriceBreakdown(SubscriptionServicePricingAndBreakdown $breakdown)
    {
        $this->breakdown = $breakdown;
        return $this;
    }

    /**
     * @param SubscriptionOrder $subscription_order
     * @return SubscriptionOrder
     */
    protected function setPricingData(SubscriptionOrder $subscription_order)
    {
        $subscription_order->discount = $this->breakdown->getDiscount();
        $subscription_order->discount_percentage = $this->breakdown->getIsDiscountPercentage();
        $subscription_order->service_details = $this->breakdown->toJson();
        return $subscription_order;
    }
}
