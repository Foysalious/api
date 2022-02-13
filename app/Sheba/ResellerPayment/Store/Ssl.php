<?php

namespace Sheba\ResellerPayment\Store;

use Sheba\Dal\GatewayAccount\Contract as GatewayAccountRepo;
use Sheba\Payment\Exceptions\InvalidConfigurationException;
use Sheba\Payment\Exceptions\StoreNotFoundException;
use Sheba\Payment\Methods\Ssl\Stores\DynamicSslStore;
use Sheba\Payment\Methods\Ssl\Stores\DynamicSslStoreConfiguration;
use Sheba\ResellerPayment\EncryptionAndDecryption;
use Sheba\ResellerPayment\Exceptions\ResellerPaymentException;
use Sheba\ResellerPayment\Exceptions\StoreAccountNotFoundException;
use Sheba\ResellerPayment\Statics\StoreConfigurationStatic;

class Ssl extends PaymentStore
{
    protected $key='ssl';
    /** @var DynamicSslStoreConfiguration $store */
    private $store;


    public static function buildData($static_data, $dynamic_configuration)
    {
        foreach ($static_data as &$data) {
            $field_name = $data["id"];
            if($data["input_type"] === "password") continue;
            $data["data"] = $dynamic_configuration ? $dynamic_configuration->$field_name : "";
        }

        return $static_data;
    }

    /**
     * @return void
     * @throws InvalidConfigurationException
     * @throws StoreNotFoundException
     */
    public function postConfiguration()
    {
        $data = $this->makeStoreAccountData();
        $this->test();
        $this->saveStore($data);
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
        $this->store = (new DynamicSslStoreConfiguration())->setStoreId($this->data->storeId)->setPassword($this->data->password)
            ->setRefundUrl($static_configuration["refund_url"])->setOrderValidationUrl($static_configuration["order_validation_url"])
            ->setSessionUrl($static_configuration["session_url"]);
        return $this->store->toArray();
    }

    private function makeStoreAccountData(): array
    {
        $configuration = json_encode($this->makeAndGetConfigurationData());
        return $this->getAndSetConfiguration($configuration);
    }

    /**
     * @return void
     * @throws InvalidConfigurationException
     * @throws StoreNotFoundException
     */
    public function test()
    {
        /** @var \Sheba\Payment\Methods\Ssl\Ssl $ssl_method */
        $ssl_method = app()->make(\Sheba\Payment\Methods\Ssl\Ssl::class);
        $ssl_method->setStore((new DynamicSslStore($this->partner))->setStoreAccount($this->store->toArray()))->testInit($this->conn_data);
    }

    /**
     * @param $status
     * @return void
     * @throws ResellerPaymentException
     * @throws StoreAccountNotFoundException
     */
    public function account_status_update($status)
    {
        $storeAccount = $this->partner->pgwGatewayAccounts()->where("gateway_type_id", $this->gateway_id)->first();
        if(!$storeAccount) throw new StoreAccountNotFoundException();
        if($status == $storeAccount->status) throw new ResellerPaymentException("The account is already in this status");
        $storeAccount->status = $status;
        $storeAccount->save();
    }

}