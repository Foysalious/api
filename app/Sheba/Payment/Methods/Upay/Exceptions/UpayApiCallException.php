<?php

namespace Sheba\Payment\Methods\Upay\Exceptions;

use Throwable;

class UpayApiCallException extends UpayPaymentException
{
    public function __construct($message = "Upay Api Call Exception", $code = 500, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}