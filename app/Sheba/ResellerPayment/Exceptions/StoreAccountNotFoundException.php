<?php

namespace Sheba\ResellerPayment\Exceptions;

use Throwable;

class StoreAccountNotFoundException extends ResellerPaymentException
{
    public function __construct($message = "", $code = 404, Throwable $previous = null)
    {
        if ($message == "") $message = "No payment gateway found for this partner";
        parent::__construct($message, $code, $previous);
    }
}