<?php

namespace Sheba\MerchantEnrollment\Exceptions;


use Sheba\ResellerPayment\Exceptions\ResellerPaymentException;
use Throwable;

class InvalidListInsertionException extends ResellerPaymentException
{
    public function __construct($message = "Trying to insert invalid list item", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
