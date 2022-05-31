<?php

namespace Sheba\QRPayment\Exceptions;

use Throwable;

class InvalidQRPaymentMethodException extends QRException
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "Requested payment method not found";
        parent::__construct($message, $code, $previous);
    }
}