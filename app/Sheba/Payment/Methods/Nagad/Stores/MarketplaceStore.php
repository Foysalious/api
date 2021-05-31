<?php namespace Sheba\Payment\Methods\Nagad\Stores;

class MarketplaceStore extends NagadStore
{
    const NAME = "marketplace";

    public function __construct()
    {
        $this->baseUrl = config('payment.nagad.stores.marketplace.base_url');
        $this->merchantId = config('payment.nagad.stores.marketplace.merchant_id');
        $this->contextPath = config('payment.nagad.stores.marketplace.context_path');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
