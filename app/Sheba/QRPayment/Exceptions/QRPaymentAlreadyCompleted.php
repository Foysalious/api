<?php

namespace Sheba\QRPayment\Exceptions;

use Throwable;

class QRPaymentAlreadyCompleted extends QRException
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "This QR payment is already completed";
        parent::__construct($message, $code, $previous);
    }
}