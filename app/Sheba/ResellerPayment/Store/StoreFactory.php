<?php

namespace Sheba\ResellerPayment\Store;

class StoreFactory
{
    private $key;

    /**
     * @param mixed $key
     * @return StoreFactory
     */
    public function setKey($key): StoreFactory
    {
        $this->key = $key;
        return $this;
    }

    public function get()
    {
        $storeClassPath = "Sheba\\ResellerPayment\\Store\\";
        $class = "$storeClassPath".ucfirst($this->key);
        return app($class);
    }
}