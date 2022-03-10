<?php namespace Sheba\SubscriptionOrderRequest;


use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class Store
{
    private $subscriptionOrderId;
    /** @var array*/
    private $partners;

    /**
     * @param $subscriptionOrderId
     * @return Store
    */
    public function setSubscriptionOrderId($subscriptionOrderId)
    {
        $this->subscriptionOrderId = $subscriptionOrderId;
        return $this;
    }

    /**
     * @param array $partners
     * @return Store
     */
    public function setPartners($partners)
    {
        $this->partners = $partners;
        return $this;
    }

    public function set()
    {
        /** @var Repository $store */
        $store = Cache::store('redis');
        $store->put($this->getCacheName(), json_encode($this->partners), now()->addHour());
    }

    /**
     * @return array|null
     */
    public function get()
    {
        /** @var Repository $store */
        $store = Cache::store('redis');
        $data = $store->get($this->getCacheName());
        return $data ? json_decode($data) : null;
    }

    private function getCacheName()
    {
        return sprintf("%s::%d", "subscription_order_requests", $this->subscriptionOrderId);
    }
}