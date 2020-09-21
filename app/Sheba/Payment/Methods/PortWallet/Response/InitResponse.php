<?php namespace Sheba\Payment\Methods\PortWallet\Response;

use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class InitResponse extends PaymentMethodResponse
{
    public function hasSuccess()
    {
        return $this->response->result == "success";
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->response->data->invoice_id;
        $success->details = $this->response;
        $success->redirect_url = $this->response->data->action->url;
        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->id = null;
        $error->details = $this->response;
        return $error;
    }
}
