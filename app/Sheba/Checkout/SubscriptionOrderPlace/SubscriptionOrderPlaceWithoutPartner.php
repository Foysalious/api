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
     * @return SubscriptionOrder
     */
    public function place()
    {
        $subscription_order = null;

        DB::transaction(function () use (&$subscription_order) {
            $subscription_order = parent::place();
            $this->requestGenerator->setSubscriptionOrder($subscription_order)->generate();
            $subscription_order = $subscription_order->fresh();
            if ($subscription_order->subscriptionOrderRequests()->count() == 0) $this->setSubscriptionOrderStatusToNotResponded($subscription_order);
        });

        return $subscription_order;
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
