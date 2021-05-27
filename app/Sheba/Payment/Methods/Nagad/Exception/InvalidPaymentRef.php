<?php namespace Sheba\Payment\Methods\Nagad\Exception;

use Exception;
use Throwable;

class InvalidPaymentRef extends Exception
{
    public function __construct($message = "Invalid Payment Ref", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
