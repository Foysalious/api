<?php

namespace Sheba\PayCharge\Adapters;


use Sheba\PayCharge\PayChargable;

interface PayChargableAdapter
{
    public function getPayable(): PayChargable;
}