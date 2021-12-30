<?php

namespace Sheba\ResellerPayment\Exceptions;

use Throwable;

class InvalidKeyException extends ResellerPaymentException
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "Invalid Payment Method Key";
        parent::__construct($message, $code, $previous);
    }
}