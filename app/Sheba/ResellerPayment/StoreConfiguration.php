<?php

namespace Sheba\ResellerPayment;

use App\Models\Partner;
use Sheba\ResellerPayment\Store\PaymentStore;
use Sheba\ResellerPayment\Store\StoreFactory;

class StoreConfiguration
{
    private $key;
    /**
     * @var Partner
     */
    private $partner;

    public function __construct()
    {
    }

    /**
     * @param mixed $key
     * @return StoreConfiguration
     */
    public function setKey($key): StoreConfiguration
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return StoreConfiguration
     */
    public function setPartner($partner): StoreConfiguration
    {
        $this->partner = $partner;
        return $this;
    }

    public function getConfiguration()
    {
        /** @var PaymentStore $store */
        $store = (new StoreFactory())->setKey($this->key)->get();
        return $store->setPartner($this->partner)->setKey($this->key)->getConfiguration();
    }

}