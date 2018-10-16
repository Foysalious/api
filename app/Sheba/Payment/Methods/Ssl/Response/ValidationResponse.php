<?php

namespace Sheba\Payment\Methods\Ssl\Response;


use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class ValidationResponse extends PaymentMethodResponse
{
    public function hasSuccess()
    {
        return $this->response->status == 'VALID' || $this->response->status == 'VALIDATED';
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        // TODO: Implement getSuccess() method.
    }

    public function getError(): PaymentMethodErrorResponse
    {
        // TODO: Implement getError() method.
    }
}