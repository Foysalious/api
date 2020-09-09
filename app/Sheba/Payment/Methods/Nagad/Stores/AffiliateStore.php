<?php


namespace Sheba\Payment\Methods\Nagad\Stores;


use Sheba\Payment\Methods\Nagad\Stores\NagadStore;

class AffiliateStore extends NagadStore
{
    const NAME = "affiliate";

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