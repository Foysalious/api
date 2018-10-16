<?php

namespace Sheba\Payment\Methods\Response;

abstract class PaymentMethodResponse
{
    protected $response;

    public function setResponse($response)
    {
        $this->response = $response;
    }

    abstract public function hasSuccess();

    abstract public function getSuccess(): PaymentMethodSuccessResponse;

    abstract public function getError(): PaymentMethodErrorResponse;
}