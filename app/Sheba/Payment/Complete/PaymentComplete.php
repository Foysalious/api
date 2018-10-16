<?php

namespace Sheba\Payment\Complete;


use App\Models\Payment;

abstract class PaymentComplete
{
    /** @var Payment $partner_order_payment */
    protected $payment;

    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }

    public abstract function complete();
}