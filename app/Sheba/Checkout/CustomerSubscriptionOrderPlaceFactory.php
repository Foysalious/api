<?php namespace Sheba\Checkout;

use App\Models\CustomerDeliveryAddress;
use Illuminate\Http\Request;
use Sheba\Checkout\Services\SubscriptionServicePricingAndBreakdown;

class CustomerSubscriptionOrderPlaceFactory extends SubscriptionOrderPlaceAbstractFactory
{
    protected function getCreator(Request $request)
    {
        if($request->has('partner')) {
            $creator = new SubscriptionOrderPlaceWithPartner();
        } else {
            $creator = new SubscriptionOrderPlaceWithOutPartner();
            $creator->setPriceBreakdown(new SubscriptionServicePricingAndBreakdown());
        }
        return $creator;
    }

    /**
     * @param Request $request
     */
    protected function buildRequest(Request $request)
    {
        $address = CustomerDeliveryAddress::withTrashed()->where('id', $request->address_id)->first();
        $this->subscriptionOrderRequest->setRequest($request)->setSalesChannel($request->sales_channel)
            ->setCustomer($request->customer)->setAddress($address)->setDeliveryMobile($request->mobile)
            ->setDeliveryName($request->name)->setUser($request->customer)
            ->prepareObject();
    }
}
