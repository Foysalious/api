<?php

namespace Sheba\Payment\Adapters\PayChargable;

use Sheba\Payment\PayChargable;

interface PayChargableAdapter
{
    public function getPayable(): PayChargable;
}