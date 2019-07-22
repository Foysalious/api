<?php namespace Sheba\Payment\Exceptions;

use Exception;
use Throwable;

class PaymentAmountNotSet extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if ($message == "") $message = "Payment amount is not set";
        $code = 404;
        parent::__construct($message, $code, $previous);
    }

}
