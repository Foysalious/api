<?php namespace Sheba\PartnerOrderRequest;


use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class Store
{
    private $partnerOrderId;
    /** @var array */
    private $partners;

    /**
     * @param mixed $partnerOrderId
     * @return Store
     */
    public function setPartnerOrderId($partnerOrderId)
    {
        $this->partnerOrderId = $partnerOrderId;
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
        $store->put($this->getCacheName(), json_encode($this->partners), $this->getExpirationTimeInSeconds() / 60);
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
        return sprintf("%s::%d", "order_requests", $this->partnerOrderId);
    }

    private function getExpirationTimeInSeconds()
    {
        return 60 * 60;
    }

}