<?php


namespace Sheba\Payment\Methods\Ebl;


use App\Models\Payable;
use App\Models\Payment;
use Sheba\Payment\Methods\PaymentMethod;

class Ebl extends PaymentMethod
{

    public function init(Payable $payable): Payment
    {
        // TODO: Implement init() method.
    }

    public function validate(Payment $payment): Payment
    {
        // TODO: Implement validate() method.
    }

    public function getMethodName()
    {
        // TODO: Implement getMethodName() method.
    }
}
