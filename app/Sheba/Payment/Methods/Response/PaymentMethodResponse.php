<?php

namespace Sheba\Payment\Methods\Response;

use App\Models\Payment;

abstract class PaymentMethodResponse
{
    protected $response;
    protected $payment;

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }

    abstract public function hasSuccess();

    abstract public function getSuccess(): PaymentMethodSuccessResponse;

    abstract public function getError(): PaymentMethodErrorResponse;
}