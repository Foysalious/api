<?php

namespace Sheba\Payment\Methods\Bkash\Stores;

use Sheba\Bkash\Modules\BkashAuthBuilder;

class BkashManagerStore extends BkashStore
{
    const NAME='smanager';
    public function __construct()
    {
        $this->auth = BkashAuthBuilder::sManagerStore();
    }

    function getName(): string
    {
       return self::NAME;
    }
}