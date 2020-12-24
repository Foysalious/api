<?php namespace Sheba\Payment\Methods\PortWallet\Response;

use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class RefundResponse extends PaymentMethodResponse
{
    public function hasSuccess()
    {
        return strtoupper($this->response->result) == "SUCCESS";
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->response->data->order->invoice_id;
        $success->refund_id = $this->response->data->order->invoice_id;
        $success->details = $this->response;
        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->id = $this->response->data->order->invoice_id;
        $error->details = $this->response;
        return $error;
    }

    public function getErrorReason()
    {
        return $this->response->error->explanation;
    }
}
