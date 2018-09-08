<?php

namespace Sheba\PayCharge\Methods;


use Sheba\PayCharge\PayChargable;

class Wallet implements PayChargeMethod
{

    public function init(PayChargable $payChargable)
    {
        return true;
    }

    public function validate($payment)
    {
        return true;
    }

    public function formatTransactionData($method_response)
    {
        // TODO: Implement formatTransactionData() method.
    }
}