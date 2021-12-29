<?php

namespace Sheba\ResellerPayment\Exceptions;

use Throwable;

class StoreValidationException extends \Exception
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "Invalid input data";
        parent::__construct($message, $code, $previous);
    }
}