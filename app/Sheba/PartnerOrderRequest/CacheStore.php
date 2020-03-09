<?php namespace Sheba\PartnerOrderRequest;


use Illuminate\Support\Facades\Redis;

class CacheStore
{
    private $partnerOrderId;
    /** @var array */
    private $partners;
    /** @var Redis */
    private $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param mixed $partnerOrderId
     * @return CacheStore
     */
    public function setPartnerOrderId($partnerOrderId)
    {
        $this->partnerOrderId = $partnerOrderId;
        return $this;
    }

    /**
     * @param array $partners
     * @return CacheStore
     */
    public function setPartners($partners)
    {
        $this->partners = $partners;
        return $this;
    }

    public function get()
    {

    }

}