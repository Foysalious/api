<?php namespace Sheba\Payment\Methods\Nagad\Stores;

class DefaultStore extends NagadStore
{
    const NAME = "default";

    public function __construct()
    {
        $this->baseUrl = config('payment.nagad.stores.default.base_url');
        $this->merchantId = config('payment.nagad.stores.default.merchant_id');
        $this->publicKey = file_get_contents(config('payment.nagad.stores.default.public_key_path'));
        $this->privateKey = file_get_contents(config('payment.nagad.stores.default.private_key_path'));
        $this->contextPath = config('payment.nagad.stores.default.context_path');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
