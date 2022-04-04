<?php

namespace Sheba\ResellerPayment\Store;

use Sheba\Payment\Exceptions\InvalidConfigurationException;
use Sheba\Payment\Methods\ShurjoPay\ShurjopayDynamicAuth;
use Sheba\Payment\Methods\ShurjoPay\Stores\ShurjopayDynamicStore;
use Sheba\Payment\Methods\Ssl\Stores\DynamicSslStoreConfiguration;
use Sheba\ResellerPayment\Statics\StoreConfigurationStatic;
use Sheba\TPProxy\TPProxyServerError;

class Shurjopay extends PaymentStore
{
    protected $key='shurjopay';
    private $store;

    /**
     * @return void
     * @throws InvalidConfigurationException|TPProxyServerError
     */
    public function postConfiguration()
    {
        $data = $this->makeStore();
        dd($data);
        if(!isset($this->data->status) || $this->data->status)
            $this->test();
        $this->saveStore($data);
    }

    /**
     * @return void
     * @throws InvalidConfigurationException|TPProxyServerError
     */
    public function test()
    {
        /** @var \Sheba\Payment\Methods\ShurjoPay\ShurjoPay $method */
        $method = app()->make(\Sheba\Payment\Methods\ShurjoPay\ShurjoPay::class);
        $storeCredentials = ($this->store->getAuth()->toArray());
        $method->testInit($storeCredentials);
    }

    /**
     * @param $storedConfig
     * @return object
     */
    public function getDynamicStoredConfiguration($storedConfig)
    {
        $auth = (new ShurjopayDynamicStore())->setPartner($this->partner)->setAuthFromEncryptedConfig($storedConfig)->getAuth();
        return (object)$auth->toArray();
    }

    private function makeStore(): array
    {
        $this->data->configuration_data->password = htmlspecialchars_decode($this->data->configuration_data->password);
        $this->data->configuration_data->storeId = htmlspecialchars_decode($this->data->configuration_data->storeId);
        $this->store = (new ShurjopayDynamicStore())->setPartner($this->partner)->setAuthFromConfig((array)$this->data->configuration_data);
        $auth = $this->store->getAuth();
        return $this->getAndSetConfiguration(json_encode($auth->toArray()));
    }

    public function getConfiguration()
    {
        $data = (new StoreConfigurationStatic())->getStoreConfiguration($this->key);
        $storeAccount = $this->getStoreAccount();
        $storedConfiguration = $storeAccount ? $storeAccount->configuration : "";
        $dynamicSslConfiguration = (new DynamicSslStoreConfiguration($storedConfiguration))->getConfiguration();
        return $this->buildData($data, $dynamicSslConfiguration);
    }

    public function buildData($static_data, $dynamic_configuration)
    {
        foreach ($static_data as &$data) {
            $field_name = $data["id"];
            if($data["input_type"] === "password") continue;
            $data["data"] = $dynamic_configuration ? $dynamic_configuration->$field_name : "";
        }

        return $static_data;
    }

    public function account_status_update($status)
    {
        // TODO: Implement account_status_update() method.
    }

    public function saveStore($data)
    {
        $storeAccount = $this->partner->pgwStoreAccounts()->where("pgw_store_id", $this->gateway_id)->first(); //for live
//        $storeAccount = $this->partner->pgwStoreAccounts()->where("gateway_type_id", $this->gateway_id)->first(); //for dev
        if (!empty($storeAccount)) {
            $storeAccount->configuration = $data["configuration"];
            if (isset($data['status'])) {
                $storeAccount->status = $data['status'];
            }
            $storeAccount->save();
        } else {
            $pgw_store_repo = app()->make(GatewayAccountRepo::class);
            $pgw_store_repo->create($data);
        }
    }
}