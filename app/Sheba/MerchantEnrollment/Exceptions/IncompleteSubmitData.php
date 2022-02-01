<?php

namespace Sheba\MerchantEnrollment\Exceptions;

use Sheba\ResellerPayment\Exceptions\ResellerPaymentException;
use Throwable;

class IncompleteSubmitData extends ResellerPaymentException
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "Invalid data. Can not submit application";
        parent::__construct($message, $code, $previous);
    }
}