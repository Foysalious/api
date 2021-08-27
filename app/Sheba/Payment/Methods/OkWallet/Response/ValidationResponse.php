<?php namespace Sheba\Payment\Methods\OkWallet\Response;

use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class ValidationResponse extends PaymentMethodResponse
{
    public function hasSuccess()
    {
        return $this->response && $this->response['RESCODE'] == 2000;
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->response['TRXNNO'];
        $success->details = $this->response;
        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->id = isset($this->response['OKTRXNID']) ? $this->response['OKTRXNID'] : null;
        $error->details = $this->response;
        return $error;
    }
}
