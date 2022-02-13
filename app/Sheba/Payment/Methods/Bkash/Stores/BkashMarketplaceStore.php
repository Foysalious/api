<?php

namespace Sheba\Payment\Methods\Bkash\Stores;

use Sheba\Bkash\Modules\BkashAuthBuilder;

class BkashMarketplaceStore extends BkashStore
{
    const NAME = 'marketplace';

    public function __construct()
    {
        $this->auth = BkashAuthBuilder::marketplaceStore();
    }

    function getName():string
    {
       return self::NAME;
    }
}