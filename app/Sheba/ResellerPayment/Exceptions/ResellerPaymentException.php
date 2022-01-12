<?php

namespace Sheba\ResellerPayment\Exceptions;

use Throwable;

class ResellerPaymentException extends \Exception
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "Reseller payment exception";
        parent::__construct($message, $code, $previous);
    }
}