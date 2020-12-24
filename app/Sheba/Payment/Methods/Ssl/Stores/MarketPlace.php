<?php namespace Sheba\Payment\Methods\Ssl\Stores;

class MarketPlace extends SslStore
{
    const NAME = "market_place";

    public function __construct()
    {
        $this->storeId            = config("payment.ssl.stores.market_place.id");
        $this->storePassword      = config("payment.ssl.stores.market_place.password");
        $this->sessionUrl         = config("payment.ssl.stores.market_place.session_url");
        $this->orderValidationUrl = config("payment.ssl.stores.market_place.order_validation_url");
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
