<?php

namespace Sheba\Payment\Methods\Cbl\Response;


use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class InitResponse extends PaymentMethodResponse
{

    public function hasSuccess()
    {
        return isset($this->response->Response) && $this->response->Response->Status == '00';
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->response->Response->Order->SessionID->__toString();
        $success->details = $this->response;
        $success->redirect_url = $this->response->Response->Order->URL->__toString();
        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->id = isset($this->response->Response->Order->SessionID) ? $this->response->Response->Order->SessionID->__toString() : null;
        $error->details = $this->response;
        return $error;
    }
}