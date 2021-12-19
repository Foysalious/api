<?php namespace Sheba\Checkout\SubscriptionOrderPlace;

use App\Models\CustomerDeliveryAddress;
use Illuminate\Http\Request;
use Sheba\Checkout\PriceBreakdownCalculators\SubscriptionPriceBreakdownCalculator;
use Sheba\Checkout\Requests\SubscriptionOrderPartnerListRequest;
use Sheba\Dal\Discount\InvalidDiscountType;

class CustomerSubscriptionOrderPlaceFactory extends SubscriptionOrderPlaceAbstractFactory
{
    /** @var SubscriptionPriceBreakdownCalculator */
    private $priceBreakdownCalculator;

    public function __construct(SubscriptionOrderPartnerListRequest $subscription_order_request,
                                SubscriptionPriceBreakdownCalculator $calculator)
    {
        parent::__construct($subscription_order_request);
        $this->priceBreakdownCalculator = $calculator;
    }

    /**
     * @param Request $request
     * @return SubscriptionOrderPlace|SubscriptionOrderPlaceWithOutPartner|SubscriptionOrderPlaceWithPartner
     */
    protected function getCreator(Request $request)
    {
        if($request->filled('partner')) {
            /** @var SubscriptionOrderPlaceWithPartner $creator */
            $creator = app(SubscriptionOrderPlaceWithPartner::class);
        } else {
            /** @var SubscriptionOrderPlaceWithoutPartner $creator */
            $creator = app(SubscriptionOrderPlaceWithoutPartner::class);
            $price = $this->priceBreakdownCalculator->setPartnerListRequest($this->subscriptionOrderRequest)->calculate();
            $creator->setPriceBreakdown($price);
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
