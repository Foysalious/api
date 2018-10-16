<?php

namespace Sheba\Payment\Methods\Ssl\Response;


use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class ValidationResponse extends PaymentMethodResponse
{
    public function hasSuccess()
    {
        return $this->response->tran_id == $this->payment->transaction_id && ($this->response->status == 'VALID' || $this->response->status == 'VALIDATED');
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->response->tran_id;
        $success->details = $this->response;
        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->id = $this->response->tran_id;
        $error->details = $this->response;
        return $error;
    }
}