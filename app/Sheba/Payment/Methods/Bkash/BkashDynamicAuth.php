<?php

namespace Sheba\Payment\Methods\Bkash;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;
use ReflectionException;
use Sheba\Bkash\Modules\BkashAuth;
use Sheba\Payment\Exceptions\StoreNotFoundException;
use Sheba\ResellerPayment\EncryptionAndDecryption;

class BkashDynamicAuth extends BkashAuth implements Arrayable
{


    public $configuration;

    public function __construct()
    {
        $this->url = config('bkash.default_url');
    }

    /**
     * @param mixed $store
     * @return BkashDynamicAuth
     * @throws StoreNotFoundException
     */
    public function setStore($store): BkashDynamicAuth
    {

        if (empty($store)) throw new StoreNotFoundException();
        $configuration = !empty($store->configuration) ? (new EncryptionAndDecryption())->setData($store->configuration)->getDecryptedData() : "[]";
        $this->configuration = json_decode($configuration, true);
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
            if (!empty($value)) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $reflection_class = new ReflectionClass($this);
        $data             = [];
        foreach ($reflection_class->getProperties() as $item) {
            if (!$item->isProtected())
                continue;
            $data[$item->name] = $this->{$item->name};
        }
        return $data;
    }

}