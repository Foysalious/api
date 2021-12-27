<?php

namespace Sheba\Payment\Methods\Bkash;

use Illuminate\Contracts\Support\Arrayable;
use Sheba\Bkash\Modules\BkashAuth;
use Sheba\NeoBanking\PrivateGetterTrait;
use Sheba\NeoBanking\Traits\ProtectedGetterTrait;
use Sheba\Payment\Exceptions\StoreNotFoundException;

class BkashDynamicAuth extends BkashAuth implements Arrayable
{
    use PrivateGetterTrait;

    public $configuration;

    /**
     * @param mixed $store
     * @return BkashDynamicAuth
     * @throws StoreNotFoundException
     */
    public function setStore($store)
    {

        if (empty($store)) throw new StoreNotFoundException();
        $this->configuration = json_decode($store->configuration, true);
        return $this;
    }

    /**
     * @param mixed $configuration
     * @return BkashDynamicAuth
     */
    public function setConfiguration(array $configuration): BkashDynamicAuth
    {
        $this->configuration = $configuration;
        return $this;
    }


    public function buildFromConfiguration(): BkashDynamicAuth
    {

        foreach ($this->configuration as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }
        return $this;
    }


}