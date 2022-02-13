<?php

namespace Sheba\Payment\Methods\Ssl\Stores;

use Sheba\Payment\Exceptions\StoreNotFoundException;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\Methods\DynamicStore;
use Sheba\ResellerPayment\EncryptionAndDecryption;

class DynamicSslStore extends SslStore
{
    use DynamicStore;

    /**
     * @var DynamicSslStoreConfiguration
     */
    private $storeConfiguration;

    /**
     * @throws StoreNotFoundException
     */
    public function __construct($receiver)
    {
        $this->setPartner($receiver);
    }

    public function setStoreAccount($config = null)
    {
        $storeAccount = $this->getStoreAccount(PaymentStrategy::SSL);
        if (!isset($storeAccount) && !$config) throw new StoreNotFoundException();
        if (!$config) {
            $this->storeConfiguration = new DynamicSslStoreConfiguration($storeAccount->configuration);
        } else {
            $config=json_encode($config);
            $this->storeConfiguration = new DynamicSslStoreConfiguration($config,false);
        }
        $this->set();
        return $this;
    }

    /**
     * @return $this
     */
    public function set(): DynamicSslStore
    {
        $this->storeId            = $this->storeConfiguration->getStoreId();
        $this->storePassword      = $this->storeConfiguration->getPassword();
        $this->sessionUrl         = $this->storeConfiguration->getSessionUrl();
        $this->orderValidationUrl = $this->storeConfiguration->getOrderValidationUrl();
        return $this;
    }

    public function getName()
    {
        $storeAccount = $this->getStoreAccount(PaymentStrategy::SSL);
        return $storeAccount->id;
    }

    /**
     * @return string
     */
    public function getRefundUrl(): string
    {
        return $this->storeConfiguration->getRefundUrl();
    }
}