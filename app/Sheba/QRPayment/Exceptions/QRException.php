<?php

namespace Sheba\QRPayment\Exceptions;

use App\Exceptions\HttpException;
use Throwable;

class QRException extends HttpException
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        if ($message == "") $message = "QR payment exception";
        parent::__construct($message, $code, $previous);
    }
}