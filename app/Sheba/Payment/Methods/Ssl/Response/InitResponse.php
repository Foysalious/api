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
        $error->id = $this->response->sessionkey;
        $error->details = $this->response;
        $error->message = $this->response->failedreason;
        return $error;
    }
}