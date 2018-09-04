<?php

namespace Sheba\PayCharge\Complete;


use Sheba\PayCharge\PayChargable;

abstract class PayChargeComplete
{
    public abstract function complete(PayChargable $pay_chargable);
}