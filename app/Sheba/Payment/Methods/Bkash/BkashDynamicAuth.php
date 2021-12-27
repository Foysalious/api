<?php

namespace Sheba\Payment\Methods\Bkash;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;
use ReflectionException;
use Sheba\Bkash\Modules\BkashAuth;
use Sheba\NeoBanking\Traits\ProtectedGetterTrait;
use Sheba\Payment\Exceptions\StoreNotFoundException;

class BkashDynamicAuth extends BkashAuth implements Arrayable
{


    public $configuration;

    /**
     * @param mixed $store
     * @return BkashDynamicAuth
     * @throws StoreNotFoundException
     */
    public function setStore($store): BkashDynamicAuth
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

    /**
     * @return array
     */
    public function toArray(): array
    {
        $reflection_class = new ReflectionClass($this);
        $data             = [];
        foreach ($reflection_class->getProperties() as $item) {
            if (!$item->isPrivate())
                continue;
            $data[$item->name] = $this->{$item->name};
        }
        return $data;
    }

}