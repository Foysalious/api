<?php

namespace Sheba\MerchantEnrollment\Exceptions;

use Sheba\ResellerPayment\Exceptions\ResellerPaymentException;
use Throwable;

class InvalidMEFFormCategoryCodeException extends ResellerPaymentException
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "Invalid MEF Form Category Key";
        parent::__construct($message, $code, $previous);
    }
}