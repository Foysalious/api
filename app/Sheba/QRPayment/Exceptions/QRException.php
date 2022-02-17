<?php

namespace Sheba\QRPayment\Exceptions;

use Throwable;

class QRException extends \Exception
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        if ($message == "") $message = "QR payment exception";
        parent::__construct($message, $code, $previous);
    }
}