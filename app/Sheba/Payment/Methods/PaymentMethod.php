<?php namespace Sheba\Payment\Methods;

use App\Models\Payable;
use App\Models\Payment;

interface PaymentMethod
{
    public function init(Payable $payable): Payment;

    public function validate(Payment $payment);

//    public function formatTransactionData($method_response);
//
//    public function getError(): PayChargeMethodError;
}