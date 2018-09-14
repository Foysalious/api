<?php

namespace Sheba\PayCharge\Adapters\Error;


use Sheba\PayCharge\Methods\MethodError;

class SslErrorAdapter implements MethodErrorAdapter
{
    private $sslError;

    public function __construct($ssl_error)
    {
        $this->sslError = $ssl_error;
    }

    public function getError(): MethodError
    {
        $method_error = new MethodError();
        $method_error->code = '';
        $method_error->message = '';
        return $method_error;
    }
}