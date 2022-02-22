<?php

namespace Sheba\QRPayment\Exceptions;

use Throwable;

class QRPayableNotFoundException extends QRException
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "No qr payable found using this qr id";
        parent::__construct($message, $code, $previous);
    }
}