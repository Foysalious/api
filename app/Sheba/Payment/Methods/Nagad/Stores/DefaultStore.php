<?php


namespace Sheba\Payment\Methods\Nagad\Stores;


class DefaultStore extends NagadStore
{
    const NAME = "default";

    public function __construct()
    {
        $this->merchantId  = config('payment.nagad.default.affiliate.merchant_id');
        $this->publicKey   = file_get_contents(config('payment.nagad.default.affiliate.public_key_path'));
        $this->privateKey  = file_get_contents(config('payment.nagad.default.affiliate.private_key_path'));
        $this->contextPath = config('payment.nagad.default.affiliate.context_path');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

}
