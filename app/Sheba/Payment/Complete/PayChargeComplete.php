<?php

namespace Sheba\Payment\Complete;


use Sheba\Payment\PayChargable;

abstract class PayChargeComplete
{
    public abstract function complete(PayChargable $pay_chargable,$method_response);
}