<?php

namespace Sheba\ResellerPayment\Store;

use Sheba\Payment\Exceptions\InvalidConfigurationException;
use Sheba\Payment\Methods\ShurjoPay\ShurjopayDynamicAuth;
use Sheba\Payment\Methods\ShurjoPay\Stores\ShurjopayDynamicStore;
use Sheba\Payment\Methods\Ssl\Stores\DynamicSslStoreConfiguration;
use Sheba\ResellerPayment\EncryptionAndDecryption;
use Sheba\ResellerPayment\Statics\StoreConfigurationStatic;
use Sheba\TPProxy\TPProxyServerError;

class Shurjopay extends PaymentStore
{
    protected $key = 'shurjopay';
    private $store;

    protected $conn_data;

    /**
     * @return void
     * @throws InvalidConfigurationException|TPProxyServerError
     */
    public function postConfiguration()
    {
//        $data = $this->makeStore();
//            $this->test();
//        $this->saveStore($data);
        $data = $this->makeStoreAccountData();
        dd($data);
        $this->test();
        $storeAccount = $this->partner->pgwStoreAccounts()->where("pgw_store_id", $this->gateway_id)->first();
        if (isset($storeAccount)) {
            $storeAccount->configuration = $data["configuration"];
            $storeAccount->save();
        } else {
            $pgw_store_repo = app()->make(PgwStoreAccountRepo::class);
            $pgw_store_repo->create($data);
        }
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
        dd($this->data);
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
        $configuration = $this->getDynamicStoredConfiguration($storedConfiguration);
        return Shurjopay::buildData($data, $configuration);
    }

    public static function buildData($static_data, $dynamic_configuration)
    {
        foreach ($static_data as &$data) {
            $field_name = $data["id"];
            if ($data["input_type"] === "password") continue;
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

    public function makeAndGetConfigurationData(): array
    {
        $configuration = (array)$this->data;
        return (new ShurjopayDynamicAuth())->setConfiguration($configuration)->buildFromConfiguration()->toArray();
    }

    private function makeStoreAccountData(): array
    {
        $configuration = json_encode($this->makeAndGetConfigurationData());
        $this->conn_data = (new EncryptionAndDecryption())->setData($configuration)->getEncryptedData();
        return [
            "pgw_store_id" => (int)$this->gateway_id,
            "user_id" => $this->partner->id,
            "user_type" => get_class($this->partner),
            "name" => "dynamic_ssl",
            "configuration" => $this->conn_data
        ];
    }
}
