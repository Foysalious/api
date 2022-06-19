<?php

namespace Sheba\Payment\Methods\ShurjoPay;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;
use Sheba\Payment\Exceptions\StoreNotFoundException;
use Sheba\ResellerPayment\EncryptionAndDecryption;

class ShurjopayDynamicAuth implements Arrayable
{
    public $configuration;

    protected $storeId;
    protected $password;

    /**
     * @param mixed $configuration
     * @return ShurjopayDynamicAuth
     */
    public function setConfiguration($configuration): ShurjopayDynamicAuth
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @param mixed $store
     * @throws StoreNotFoundException
     */
    public function setStore($store): ShurjopayDynamicAuth
    {
        if (empty($store)) throw new StoreNotFoundException();
        $configuration = !empty($store->configuration) ? (new EncryptionAndDecryption())->setData($store->configuration)->getDecryptedData() : "[]";
        $this->configuration = json_decode($configuration, true);
        return $this;
    }

    /**
     * @return $this
     */
    public function buildFromConfiguration(): ShurjopayDynamicAuth
    {
        foreach ($this->configuration as $key => $value) {
            if (!empty($value)) {
                $this->$key = $value;
            }
        }
        return $this;
    }

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

    /**
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
