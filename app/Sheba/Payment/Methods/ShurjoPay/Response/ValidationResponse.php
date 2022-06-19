<?php namespace Sheba\Payment\Methods\ShurjoPay\Response;

use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class ValidationResponse extends PaymentMethodResponse
{
    const SUCCESS = 'Success';

    public function hasSuccess()
    {
        return $this->response && (int)($this->response->customer_order_id) == $this->payment->id && $this->response->sp_massage == self::SUCCESS;
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->response->order_id;
        $success->details = $this->response;
        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->id = isset($this->response->order_id) ? $this->response->order_id : null;
        $error->details = $this->response;
        return $error;
    }
}
