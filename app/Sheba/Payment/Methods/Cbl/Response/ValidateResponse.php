<?php namespace Sheba\Payment\Methods\Cbl\Response;


use Sheba\Payment\Methods\Response\PaymentMethodErrorResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;

class ValidateResponse extends PaymentMethodResponse
{

    public function hasSuccess()
    {
        return isset($this->response->Response->Order->row->Orderstatus) && $this->response->Response->Order->row->Orderstatus;
    }

    public function getSuccess(): PaymentMethodSuccessResponse
    {
        // TODO: Implement getSuccess() method.
    }

    public function getError(): PaymentMethodErrorResponse
    {
        // TODO: Implement getError() method.
    }
}