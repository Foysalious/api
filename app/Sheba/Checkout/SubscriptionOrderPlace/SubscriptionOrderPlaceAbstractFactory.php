<?php namespace Sheba\Checkout\SubscriptionOrderPlace;

use Illuminate\Http\Request;
use Sheba\Checkout\Requests\SubscriptionOrderPartnerListRequest;

abstract class SubscriptionOrderPlaceAbstractFactory
{
    /** @var SubscriptionOrderPartnerListRequest */
    protected $subscriptionOrderRequest;

    public function __construct(SubscriptionOrderPartnerListRequest $subscription_order_request)
    {
        $this->subscriptionOrderRequest = $subscription_order_request;
    }

    /**
     * @param Request $request
     * @return SubscriptionOrderPlace
     */
    public function get(Request $request)
    {
        $this->buildRequest($request);
        $creator = $this->getCreator($request);
        return $creator->setSubscriptionRequest($this->subscriptionOrderRequest);
    }

    /**
     * @param Request $request
     * @return SubscriptionOrderPlace
     */
    abstract protected function getCreator(Request $request);

    abstract protected function buildRequest(Request $request);
}
