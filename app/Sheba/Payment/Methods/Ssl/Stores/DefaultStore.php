<?php namespace Sheba\Payment\Methods\Ssl\Stores;

class DefaultStore extends SslStore
{
    const NAME = "default";

    public function __construct()
    {
        $this->storeId            = config("payment.ssl.stores.default.id");
        $this->storePassword      = config("payment.ssl.stores.default.password");
        $this->sessionUrl         = config("payment.ssl.stores.default.session_url");
        $this->orderValidationUrl = config("payment.ssl.stores.default.order_validation_url");
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
