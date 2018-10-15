<?php namespace Sheba\Payment\Methods;

use Sheba\Payment\PayChargable;

interface PayChargeMethod
{
    public function init(PayChargable $payChargable);

    public function validate($payment);

    public function formatTransactionData($method_response);

    public function getError(): PayChargeMethodError;
}