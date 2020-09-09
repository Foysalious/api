<?php


namespace Sheba\Payment\Methods\Nagad\Stores;


class DefaultStore extends NagadStore
{
    const NAME = "default";

    public function __construct()
    {
        $this->merchantId  = config('payment.nagad.stores.affiliate.merchant_id');
        $this->publicKey   = file_get_contents(config('payment.nagad.stores.affiliate.public_key_path'));
        $this->privateKey  = file_get_contents(config('payment.nagad.stores.affiliate.private_key_path'));
        $this->contextPath = config('payment.nagad.stores.affilate.context_path');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

}