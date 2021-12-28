<?php

namespace Sheba\ResellerPayment\Store;

abstract class PaymentStore
{
    protected $key;
    protected $partner;

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

    protected function getStoreAccount()
    {
        return $this->partner->pgwStoreAccounts()->join('pgw_stores', 'pgw_store_id', '=', 'pgw_stores.id')
            ->where('pgw_stores.key', $this->key)->first();
    }

    public abstract function getConfiguration();

}