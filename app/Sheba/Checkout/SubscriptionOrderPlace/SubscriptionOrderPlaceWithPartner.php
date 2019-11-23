<?php namespace Sheba\Checkout\SubscriptionOrderPlace;

use App\Exceptions\HyperLocationNotFoundException;
use App\Http\Controllers\Subscription\SubscriptionPartnerList;
use App\Models\Partner;
use App\Models\SubscriptionOrder;
use Sheba\Dal\Discount\InvalidDiscountType;

class SubscriptionOrderPlaceWithPartner extends SubscriptionOrderPlace
{
    /**
     * @param SubscriptionOrder $subscription_order
     * @return SubscriptionOrder
     * @throws HyperLocationNotFoundException
     * @throws InvalidDiscountType
     */
    protected function setPricingData(SubscriptionOrder $subscription_order)
    {
        $partner = $this->getPartner();
        $subscription_order->partner_id = $this->subscriptionOrderRequest->selectedPartner->id;
        $subscription_order->discount = $partner->discount;
        $subscription_order->discount_percentage = $partner->breakdown[0]['is_percentage'];
        $subscription_order->service_details = $this->formatServiceDetails($partner);
        return $subscription_order;
    }

    /**
     * @return mixed
     * @throws HyperLocationNotFoundException
     * @throws InvalidDiscountType
     */
    private function getPartner()
    {
        $partner_list = new SubscriptionPartnerList();
        $partner_list->setPartnerListRequest($this->subscriptionOrderRequest)
            ->find($this->subscriptionOrderRequest->selectedPartner->id);
        $partner_list->addPricing();
        return $partner_list->partners->first();
    }

    private function formatServiceDetails(Partner $partner)
    {
        removeRelationsAndFields($partner);
        return $partner->toJson();
    }
}
