<?php

namespace Sheba\QRPayment\Exceptions;

use Throwable;

class CustomerNotFoundException extends QRException
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "Customer not found";
        parent::__construct($message, $code, $previous);
    }
}