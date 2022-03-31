<?php namespace Sheba\Payment\Methods\Bkash\Response;

use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class ExecuteResponse extends PaymentMethodResponse
{
    public function hasSuccess()
    {
        return isset($this->response->transactionStatus) && $this->response->transactionStatus == 'Completed' &&
            ($this->payment->transaction_id == $this->response->merchantInvoiceNumber || $this->payment->transaction_id == $this->response->paymentID);
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->response->trxID;
        $success->details = $this->response;

        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->details = $this->response;
        $error->code = $this->response->errorCode;
        $error->message = $this->response->errorMessage;

        return $error;
    }
}