<?php

namespace Sheba\Payment\Methods\Ssl\Stores;

use Sheba\Payment\Exceptions\StoreNotFoundException;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\Methods\DynamicStore;
use Sheba\Payment\Methods\DynamicStoreConfiguration;

class DynamicSslStore extends SslStore
{
    use DynamicStore;

    public function __construct($receiver)
    {
        $this->setPartner($receiver);
    }

    /**
     * @param $payment_method
     * @return $this
     * @throws StoreNotFoundException
     */
    public function set($payment_method): DynamicSslStore
    {
        $storeAccount             = $this->getStoreAccount($payment_method);
        if(!isset($storeAccount)) throw new StoreNotFoundException();
        $this->storeId            = $storeAccount->store_id;
        $this->storePassword      = (new DynamicStoreConfiguration($storeAccount->configuration))->getPassword();
        $this->sessionUrl         = config("payment.ssl.stores.default.session_url");
        $this->orderValidationUrl = config("payment.ssl.stores.default.order_validation_url");
        return $this;
    }

    public function getName()
    {
//        $storeAccount        = $this->getStoreAccount(PaymentStrategy::SSL);
//        return $storeAccount->name;
    }
}