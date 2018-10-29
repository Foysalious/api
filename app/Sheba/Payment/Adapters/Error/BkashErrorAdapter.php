<?php

namespace Sheba\Payment\Adapters\Error;


use Sheba\Payment\Methods\PayChargeMethodError;

class BkashErrorAdapter implements MethodErrorAdapter
{
    private $bkashError;

    public function __construct($bkash_error)
    {
        $this->bkashError = $bkash_error;
    }

    public function getError(): PayChargeMethodError
    {
        $method_error = new PayChargeMethodError();
        $method_error->code = $this->bkashError->errorCode;
        $method_error->message = $this->bkashError->errorMessage;
        return $method_error;
    }
}