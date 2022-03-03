<?php

namespace Sheba\MerchantEnrollment\Exceptions;

use Sheba\ResellerPayment\Exceptions\ResellerPaymentException;
use Throwable;

class InvalidQRKeyException extends ResellerPaymentException
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "Invalid qr method name";
        parent::__construct($message, $code, $previous);
    }
}