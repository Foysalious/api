<?php

namespace Sheba\PayCharge\Methods;


use Sheba\PayCharge\PayChargable;

interface PayChargeMethod
{

    public function init(PayChargable $payChargable);

    public function validate();
}