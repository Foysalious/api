<?php

namespace Sheba\PayCharge\Adapters\PayChargable;

use Sheba\PayCharge\PayChargable;

interface PayChargableAdapter
{
    public function getPayable(): PayChargable;
}