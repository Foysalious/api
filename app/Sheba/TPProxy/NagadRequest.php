<?php namespace Sheba\TPProxy;

class NagadRequest extends TPRequest
{
    private $storeData;

    /**
     * @return mixed
     */
    public function getStoreData()
    {
        return $this->storeData;
    }

    /**
     * @param mixed $store_data
     */
    public function setStoreData($store_data): NagadRequest
    {
        $this->storeData = $store_data;
        return $this;
    }
}
