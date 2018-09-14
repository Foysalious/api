<?php

namespace Sheba\PayCharge\Adapters\Error;


use Sheba\PayCharge\Methods\MethodError;

class BkashErrorAdapter implements MethodErrorAdapter
{
    private $bkashError;

    public function __construct($bkash_error)
    {
        $this->bkashError = $bkash_error;
    }

    public function getError(): MethodError
    {
        $method_error = new MethodError();
        $method_error->code = $this->bkashError->errorCode;
        $method_error->message = $this->bkashError->errorMessage;
        return $method_error;
    }
}