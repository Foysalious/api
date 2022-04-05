<?php

namespace Sheba\ResellerPayment\Store;

use Sheba\Payment\Exceptions\InvalidConfigurationException;
use Sheba\Payment\Methods\ShurjoPay\ShurjopayDynamicAuth;
use Sheba\Payment\Methods\ShurjoPay\Stores\ShurjopayDynamicStore;
use Sheba\Payment\Methods\Ssl\Stores\DynamicSslStoreConfiguration;
use Sheba\ResellerPayment\EncryptionAndDecryption;
use Sheba\ResellerPayment\Statics\StoreConfigurationStatic;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\Dal\GatewayAccount\Contract as PgwGatewayAccountRepo;
use Sheba\Dal\PgwStoreAccount\Contract as PgwStoreAccountRepo;

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
        $data = $this->makeStoreAccountData();
        $this->test();
//        $storeAccount = $this->partner->pgwStoreAccounts()->where("pgw_store_id", $this->gateway_id)->first(); // for live
        $storeAccount = $this->partner->pgwGatewayAccounts()->where("gateway_type_id", $this->gateway_id)->first(); // for dev
        if(isset($storeAccount)) {
            $storeAccount->configuration = $data["configuration"];
            $storeAccount->save();
        } else {
//            $pgw_store_repo = app()->make(PgwStoreAccountRepo::class); //for live
            $pgw_store_repo = app()->make(PgwGatewayAccountRepo::class); //for dev
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
        $method->testInit((array)$this->data);
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
//        $storeAccount = $this->partner->pgwStoreAccounts()->where("pgw_store_id", $this->gateway_id)->first(); //for live
        $storeAccount = $this->partner->pgwStoreAccounts()->where("gateway_type_id", $this->gateway_id)->first(); //for dev
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
//            "pgw_store_id"  => (int)$this->gateway_id,    //for live
            "gateway_type_id"  => (int)$this->gateway_id,   //for dev
            "user_id"       => $this->partner->id,
            "user_type"     => class_basename($this->partner),
            "name"          => "dynamic_shurjopay",
            "configuration" => $this->conn_data
        ];
    }
}
