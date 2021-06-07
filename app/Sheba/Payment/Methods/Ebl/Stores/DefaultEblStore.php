<?php


namespace Sheba\Payment\Methods\Ebl\Stores;


class DefaultEblStore extends EblStore
{
    const NAME = 'default';

    public function __construct()
    {
        $this->baseUrl    = config('payment.ebl.stores.default.base_url');
        $this->profileId  = config('payment.ebl.stores.default.profile_id');
        $this->merchantID = config('payment.ebl.stores.default.merchant_id');
        $this->accessKey  = config('payment.ebl.stores.default.access_key');
        $this->secretKey  = file_get_contents(config('payment.ebl.stores.default.secret_key'));
    }

    function getName()
    {
        return self::NAME;
    }
}
