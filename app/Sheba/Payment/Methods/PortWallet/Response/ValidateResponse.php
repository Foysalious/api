<?php namespace Sheba\Payment\Methods\PortWallet\Response;


use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class ValidateResponse extends PaymentMethodResponse
{
    public function hasSuccess()
    {
        return $this->getOrderStatus() == "ACCEPTED";
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->payment->gateway_transaction_id;
        $success->details = $this->response;
        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->id = $this->payment->gateway_transaction_id;
        $error->details = $this->response;
        return $error;
    }

    public function getOrderStatus()
    {
        if (isset($this->response->data)){
            return $this->response->data->order->status;
        }
        return 'VALIDATION FAILED';
    }
}
