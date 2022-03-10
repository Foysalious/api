<?php namespace Sheba\AutoSpAssign\PartnerOrderRequest;


use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Sheba\AutoSpAssign\EligiblePartner;

class Store
{
    private $partnerOrderId;
    /** @var array */
    private $partnerIds;

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
     * @return $this
     */
    public function setAscendingSortedPartnerIds(array $partners)
    {
        $this->partnerIds = $partners;
        return $this;
    }

    public function set()
    {
        /** @var Repository $store */
        $store = Cache::store('redis');
        $store->put($this->getCacheName(), json_encode($this->partnerIds), now()->addHour());
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
}