<?php namespace Sheba\Payment\Methods\Cbl\Response;


use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class ValidateResponse extends PaymentMethodResponse
{

    public function hasSuccess()
    {
        return isset($this->response->Response->Order->row->Orderstatus) && $this->response->Response->Order->row->Orderstatus->__toString() == 'CREATED';
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        $success = new PaymentMethodSuccessResponse();
        $success->id = $this->response->Response->Order->row->SessionID->__toString();
        $success->details = $this->response;
        return $success;
    }

    public function getError(): PaymentMethodErrorResponse
    {
        $error = new PaymentMethodErrorResponse();
        $error->id = isset($this->response->Response->Order->row->SessionID) ? $this->response->Response->Order->row->SessionID->__toString() : null;
        $error->details = $this->response;
        return $error;
    }
}