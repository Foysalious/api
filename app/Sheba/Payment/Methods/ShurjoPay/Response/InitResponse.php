<?php namespace Sheba\Payment\Methods\ShurjoPay\Response;

use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class InitResponse extends PaymentMethodResponse
{
    const INITIATED = 'Initiated';

    public function hasSuccess()
    {
        return $this->response->transactionStatus == self::INITIATED;
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->response->sp_order_id;
        $success->details = $this->response;
        $success->redirect_url = $this->response->checkout_url;
        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->id = isset($this->response->sp_order_id) ? $this->response->sp_order_id : null;
        $error->details = $this->response;
        $error->message = null;
        return $error;
    }
}