<?php namespace Sheba\Payment\Methods\Nagad\Stores;

class AffiliateStore extends NagadStore
{
    const NAME = "affiliate";

    public function __construct()
    {
        $this->baseUrl = config('payment.nagad.stores.affiliate.base_url');
        $this->merchantId = config('payment.nagad.stores.affiliate.merchant_id');
        $this->publicKey = file_get_contents(config('payment.nagad.stores.affiliate.public_key_path'));
        $this->privateKey = file_get_contents(config('payment.nagad.stores.affiliate.private_key_path'));
        $this->contextPath = config('payment.nagad.stores.affiliate.context_path');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
