<?php

namespace Sheba\ResellerPayment\Store;

use Sheba\Dal\GatewayAccount\Contract as PgwGatewayAccountRepo;
use Sheba\Payment\Methods\Ssl\Stores\DynamicSslStoreConfiguration;
use Sheba\ResellerPayment\EncryptionAndDecryption;
use Sheba\ResellerPayment\Statics\StoreConfigurationStatic;

abstract class PaymentStore
{
    protected $key;
    protected $partner;
    protected $data;
    protected $gateway_id;
    protected $conn_data;
    /**
     * @param mixed $key
     * @return PaymentStore
     */
    public function setKey($key): PaymentStore
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return PaymentStore
     */
    public function setPartner($partner): PaymentStore
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): PaymentStore
    {
        $this->data = json_decode($data);
        return $this;
    }

    /**
     * @param mixed $gateway_id
     * @return PaymentStore
     */
    public function setGatewayId($gateway_id): PaymentStore
    {
        $this->gateway_id = $gateway_id;
        return $this;
    }
    public function getConfiguration()
    {
        $data = (new StoreConfigurationStatic())->getStoreConfiguration($this->key);
        $storeAccount = $this->getStoreAccount();
        $storedConfiguration = $storeAccount ? $storeAccount->configuration : "";
        $dynamicSslConfiguration = (new DynamicSslStoreConfiguration($storedConfiguration))->getConfiguration();
        return self::buildData($data, $dynamicSslConfiguration);
    }

    public static function buildData($static_data, $dynamic_configuration)
    {
        foreach ($static_data as &$data) {
            $field_name = $data["id"];
            if($field_name === "password") continue;
            $data["data"] = $dynamic_configuration ? $dynamic_configuration->$field_name : "";
        }

        return $static_data;
    }

    public function saveStore($data){
        $storeAccount = $this->partner->pgwGatewayAccounts()->where("gateway_type_id", $this->gateway_id)->first();
        if(isset($storeAccount)) {
            $storeAccount->configuration = $data["configuration"];
            $storeAccount->save();
        } else {
            $pgw_store_repo = app()->make(PgwGatewayAccountRepo::class);
            $pgw_store_repo->create($data);
        }
    }
    protected function getStoreAccount()
    {
        if(isset($this->partner))
            return $this->partner->pgwGatewayAccounts()->join('pgw_stores', 'gateway_type_id', '=', 'pgw_stores.id')
                ->where('pgw_stores.key', $this->key)->first();
        return null;
    }

    public function getAndSetConfiguration($configuration){
        $this->conn_data = (new EncryptionAndDecryption())->setData($configuration)->getEncryptedData();
        $user_types = explode('\\', get_class($this->partner));
        return [
            "gateway_type_id" => (int)$this->gateway_id,
            "user_id"         => $this->partner->id,
            "user_type"       => end($user_types),
            "name"            => "dynamic_ssl",
            "configuration"   => $this->conn_data
        ];
    }
    public abstract function postConfiguration();

    public abstract function test();

    public abstract function account_status_update($status);

}