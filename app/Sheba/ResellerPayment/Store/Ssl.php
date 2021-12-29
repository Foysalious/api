<?php

namespace Sheba\ResellerPayment\Store;

use Sheba\Dal\PgwStoreAccount\Contract as PgwStoreAccountRepo;
use Sheba\Payment\Exceptions\InvalidConfigurationException;
use Sheba\Payment\Methods\Ssl\Stores\DynamicSslStoreConfiguration;
use Sheba\ResellerPayment\Statics\StoreConfigurationStatic;

class Ssl extends PaymentStore
{
    private $conn_data;

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
            if($field_name === "password") continue;
            $data["data"] = $dynamic_configuration ? $dynamic_configuration->$field_name : "";
        }

        return $static_data;
    }

    /**
     * @return void
     * @throws InvalidConfigurationException
     */
    public function postConfiguration()
    {
        $data = $this->makeStoreAccountData();
        $this->test();
        $storeAccount = $this->partner->pgwStoreAccounts()->where("pgw_store_id", $this->gateway_id)->first();
        if(isset($storeAccount)) {
            $storeAccount->configuration = $data["configuration"];
            $storeAccount->save();
        } else {
            $pgw_store_repo = app()->make(PgwStoreAccountRepo::class);
            $pgw_store_repo->create($data);
        }
    }

    private static function staticSslConfigurations(): array
    {
        return [
            "refund_url" => config('payment.ssl.stores.default.refund_url'),
            "session_url" => config('payment.ssl.stores.default.session_url'),
            "order_validation_url" => config('payment.ssl.stores.default.order_validation_url')
        ];
    }

    public function makeAndGetConfigurationData(): array
    {
        $static_configuration = self::staticSslConfigurations();
        return (new DynamicSslStoreConfiguration())->setStoreId($this->data->storeId)->setPassword($this->data->password)
            ->setRefundUrl($static_configuration["refund_url"])->setOrderValidationUrl($static_configuration["order_validation_url"])
            ->setSessionUrl($static_configuration["session_url"])->toArray();
    }

    private function makeStoreAccountData(): array
    {
        $this->conn_data = $this->makeAndGetConfigurationData();
        return [
            "pgw_store_id"  => (int)$this->gateway_id,
            "user_id"       => $this->partner->id,
            "user_type"     => get_class($this->partner),
            "name"          => "dynamic_ssl",
            "configuration" => json_encode($this->conn_data)
        ];
    }

    /**
     * @return void
     * @throws InvalidConfigurationException
     */
    public function test()
    {
        /** @var \Sheba\Payment\Methods\Ssl\Ssl $ssl_method */
        $ssl_method = app()->make(\Sheba\Payment\Methods\Ssl\Ssl::class);
        $ssl_method->testInit(json_encode($this->conn_data));
    }

}