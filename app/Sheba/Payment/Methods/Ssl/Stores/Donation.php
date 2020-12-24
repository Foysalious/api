<?php namespace Sheba\Payment\Methods\Ssl\Stores;

class Donation extends SslStore
{
    const NAME = "donation";

    public function __construct()
    {
        $this->storeId            = config("payment.ssl.stores.donation.id");
        $this->storePassword      = config("payment.ssl.stores.donation.password");
        $this->sessionUrl         = config("payment.ssl.stores.donation.session_url");
        $this->orderValidationUrl = config("payment.ssl.stores.donation.order_validation_url");
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
