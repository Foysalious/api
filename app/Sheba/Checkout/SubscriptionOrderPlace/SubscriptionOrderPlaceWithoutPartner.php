<?php namespace Sheba\Checkout\SubscriptionOrderPlace;

use App\Models\SubscriptionOrder;
use Illuminate\Support\Facades\DB;
use Sheba\Checkout\Services\SubscriptionServicePricingAndBreakdown;
use Sheba\Location\Geo;
use Sheba\SubscriptionOrderRequest\Generator;

class SubscriptionOrderPlaceWithoutPartner extends SubscriptionOrderPlace
{
    /** @var Generator */
    private $requestGenerator;

    /** @var SubscriptionServicePricingAndBreakdown */
    protected $breakdown;

    public function __construct(Generator $generator)
    {
        $this->requestGenerator = $generator;
    }

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
