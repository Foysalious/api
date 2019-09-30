<?php

namespace Sheba\Payment\Methods\Ssl\Response;

use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class InitResponse extends PaymentMethodResponse
{

    public function hasSuccess()
    {
        return $this->response->status == 'SUCCESS';
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->response->sessionkey;
        $success->details = $this->response;
        $success->redirect_url = $this->response->GatewayPageURL;
        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->id = isset($this->response->sessionkey) ? $this->response->sessionkey : null;
        $error->details = $this->response;
        $error->message = isset($this->response->failedreason) ? $this->response->failedreason : null;
        return $error;
    }
}