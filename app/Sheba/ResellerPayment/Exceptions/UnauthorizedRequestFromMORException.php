<?php namespace App\Sheba\ResellerPayment\Exceptions;

use Sheba\ResellerPayment\Exceptions\ResellerPaymentException;
use Throwable;

class UnauthorizedRequestFromMORException extends ResellerPaymentException
{
    public function __construct($message = "Api access key mismatched", $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}