<?php

namespace Sheba\Payment\Methods\Bkash\Stores;

use Sheba\Bkash\Modules\BkashAuthBuilder;

class BkashDefaultStore extends BkashStore
{
    const NAME = 'default';

    public function __construct()
    {
        $this->auth = BkashAuthBuilder::sManagerStore();
    }

    function getName(): string
    {
        return self::NAME;
    }
}