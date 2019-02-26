<?php namespace Sheba\Checkout;


use App\Http\Controllers\Subscription\SubscriptionPartnerList;
use App\Models\Partner;
use Sheba\Checkout\Requests\SubscriptionOrderRequest;

class SubscriptionOrder
{
    private $subscriptionOrderRequest;

    public function setSubscriptionRequest(SubscriptionOrderRequest $subscriptionOrderRequest)
    {
        $this->subscriptionOrderRequest = $subscriptionOrderRequest;
        return $this;
    }

    public function place()
    {
        $partner = $this->getPartner();
        $subscription_order = new \App\Models\SubscriptionOrder();
        $subscription_order->billing_cycle = $this->subscriptionOrderRequest->subscriptionType;
        $subscription_order->customer_id = $this->subscriptionOrderRequest->customer->id;
        $subscription_order->location_id = $this->subscriptionOrderRequest->address->location_id;
        $subscription_order->delivery_address_id = $this->subscriptionOrderRequest->address->id;
        $subscription_order->location_id = $this->subscriptionOrderRequest->address->location_id;
        $subscription_order->sales_channel = $this->subscriptionOrderRequest->salesChannel;
        $subscription_order->partner_id = $this->subscriptionOrderRequest->selectedPartner->id;
        $subscription_order->delivery_mobile = $this->subscriptionOrderRequest->deliveryMobile;
        $subscription_order->delivery_name = $this->subscriptionOrderRequest->deliveryName;
        $subscription_order->additional_info = $this->subscriptionOrderRequest->additionalInfo;
        $subscription_order->discount = $partner->discount;
        $subscription_order->discount_percentage = $partner->breakdown[0]['is_percentage'];
        $subscription_order->category_id = $this->subscriptionOrderRequest->selectedCategory->id;
        $subscription_order->billing_cycle_start = $this->subscriptionOrderRequest->billingCycleStart;
        $subscription_order->billing_cycle_end = $this->subscriptionOrderRequest->billingCycleEnd;
        $subscription_order->service_details = $this->formatServiceDetails($partner);
        $subscription_order->schedules = $this->formatSchedules();
        $subscription_order->status = 'requested';
        $subscription_order->save();
        return $subscription_order;
    }

    private function getPartner()
    {
        $partner_list = new SubscriptionPartnerList();
        $partner_list->setPartnerListRequest($this->subscriptionOrderRequest)->find($this->subscriptionOrderRequest->selectedPartner->id);
        $partner_list->addPricing();
        return $partner_list->partners->first();
    }

    private function formatSchedules()
    {
        $schedules = [];
        foreach ($this->subscriptionOrderRequest->scheduleDate as $date) {
            array_push($schedules, ['date' => $date, 'time' => $this->subscriptionOrderRequest->scheduleTime]);
        }
        return json_encode($schedules);
    }

    private function formatServiceDetails(Partner $partner)
    {
        removeRelationsAndFields($partner);
        return $partner->toJson();
    }
}