<?php

namespace Sheba\ResellerPayment\Store;

use Sheba\Payment\Exceptions\InvalidConfigurationException;
use Sheba\Payment\Exceptions\InvalidStoreConfiguration;
use Sheba\Payment\Exceptions\StoreNotFoundException;
use Sheba\Payment\Methods\Bkash\BkashDynamicAuth;
use Sheba\Payment\Methods\Bkash\Stores\BkashDynamicStore;

class Bkash extends PaymentStore
{
    protected $key = 'bkash';
    /**
     * @var BkashDynamicStore
     */
    private $store;

    /**
     * @throws InvalidStoreConfiguration
     * @throws StoreNotFoundException
     * @throws InvalidConfigurationException
     */
    public function postConfiguration()
    {
        $data = $this->makeStore();
        $this->test();
        $this->saveStore($data);
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function test()
    {
        /** @var \Sheba\Payment\Methods\Bkash\Bkash $method */
        $method = app(\Sheba\Payment\Methods\Bkash\Bkash::class);
        $method->testInit($this->store);
    }

    /**
     *
     */
    private function makeStore()
    {
        $this->store = (new BkashDynamicStore())->setPartner($this->partner)->setAuthFromConfig((array)$this->data->configuration_data);
        /** @var BkashDynamicAuth $auth */
        $auth = $this->store->getAuth();
        return $this->getAndSetConfiguration(json_encode($auth->toArray()));
    }
}