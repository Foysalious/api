<?php

namespace Sheba\ResellerPayment\Store;

abstract class PaymentStore
{
    protected $key;
    protected $partner;
    protected $data;
    protected $gateway_id;

    /**
     * @param mixed $key
     * @return PaymentStore
     */
    public function setKey($key): PaymentStore
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return PaymentStore
     */
    public function setPartner($partner): PaymentStore
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): PaymentStore
    {
        $this->data = json_decode($data);
        return $this;
    }

    /**
     * @param mixed $gateway_id
     * @return PaymentStore
     */
    public function setGatewayId($gateway_id): PaymentStore
    {
        $this->gateway_id = $gateway_id;
        return $this;
    }

    protected function getStoreAccount()
    {
        if(isset($this->partner))
            return $this->partner->pgwStoreAccounts()->join('pgw_stores', 'pgw_store_id', '=', 'pgw_stores.id')
                ->where('pgw_stores.key', $this->key)->first();
    }

    public abstract function getConfiguration();

    public abstract function postConfiguration();

}