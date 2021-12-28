<?php

namespace Sheba\ResellerPayment;

use Sheba\ResellerPayment\Statics\StoreConfigurationStatic;

class StoreConfiguration
{
    private $key;

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

    public function getConfiguration()
    {
        return (new StoreConfigurationStatic())->getStoreConfiguration($this->key);
    }
}