<?php

namespace Sheba\Payment\Adapters\Error;


use Sheba\Payment\Methods\PayChargeMethodError;

class SslErrorAdapter implements MethodErrorAdapter
{
    private $sslError;

    public function __construct($ssl_error)
    {
        $this->sslError = $ssl_error;
    }

    public function getError(): PayChargeMethodError
    {
        $method_error = new PayChargeMethodError();
        $method_error->code = '';
        $method_error->message = '';
        return $method_error;
    }
}