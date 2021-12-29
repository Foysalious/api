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
    private $request_data;
    private $gateway_id;

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

    public function storeConfiguration()
    {
        /** @var PaymentStore $store */
        $store = (new StoreFactory())->setKey($this->key)->get();
        $store->setData($this->request_data)->setPartner($this->partner)->setGatewayId($this->gateway_id)->postConfiguration();
    }

    /**
     * @param mixed $request_data
     * @return StoreConfiguration
     */
    public function setRequestData($request_data): StoreConfiguration
    {
        $this->request_data = $request_data;
        return $this;
    }

    /**
     * @param mixed $gateway_id
     * @return StoreConfiguration
     */
    public function setGatewayId($gateway_id): StoreConfiguration
    {
        $this->gateway_id = $gateway_id;
        return $this;
    }

}