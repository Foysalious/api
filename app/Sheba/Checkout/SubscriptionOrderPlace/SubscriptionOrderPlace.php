<?php namespace Sheba\Checkout\SubscriptionOrderPlace;

use App\Models\SubscriptionOrder;
use Sheba\Checkout\Requests\SubscriptionOrderPartnerListRequest;
use Sheba\Dal\SubscriptionOrder\Statuses;

abstract class SubscriptionOrderPlace
{
    /** @var SubscriptionOrderPartnerListRequest */
    protected $subscriptionOrderRequest;

    public function setSubscriptionRequest(SubscriptionOrderPartnerListRequest $subscriptionOrderRequest)
    {
        $this->subscriptionOrderRequest = $subscriptionOrderRequest;
        return $this;
    }

    /**
     * @return SubscriptionOrder
     */
    public function place()
    {
        $subscription_order = new SubscriptionOrder();
        $subscription_order->billing_cycle = $this->subscriptionOrderRequest->subscriptionType;
        $subscription_order->customer_id = $this->subscriptionOrderRequest->customer->id;
        $subscription_order->user_id = $this->subscriptionOrderRequest->user->id;
        $subscription_order->user_type = get_class($this->subscriptionOrderRequest->user);
        $subscription_order->location_id = $this->subscriptionOrderRequest->location;
        $subscription_order->delivery_address_id = $this->subscriptionOrderRequest->address->id;
        $subscription_order->sales_channel = $this->subscriptionOrderRequest->salesChannel;
        $subscription_order->delivery_mobile = $this->subscriptionOrderRequest->deliveryMobile;
        $subscription_order->delivery_name = $this->subscriptionOrderRequest->deliveryName;
        $subscription_order->additional_info = $this->subscriptionOrderRequest->additionalInfo;
        $subscription_order->category_id = $this->subscriptionOrderRequest->selectedCategory->id;
        $subscription_order->billing_cycle_start = $this->subscriptionOrderRequest->billingCycleStart;
        $subscription_order->billing_cycle_end = $this->subscriptionOrderRequest->billingCycleEnd;
        $subscription_order->schedules = $this->formatSchedules();
        $subscription_order->status = Statuses::REQUESTED;
        $subscription_order = $this->setPricingData($subscription_order);
        $subscription_order->save();
        return $subscription_order;
    }

    /**
     * @param SubscriptionOrder $subscription_order
     * @return SubscriptionOrder
     */
    abstract protected function setPricingData(SubscriptionOrder $subscription_order);

    private function formatSchedules()
    {
        $schedules = [];
        foreach ($this->subscriptionOrderRequest->scheduleDate as $date) {
            array_push($schedules, ['date' => $date, 'time' => $this->subscriptionOrderRequest->scheduleTime]);
        }
        return json_encode($schedules);
    }
}
