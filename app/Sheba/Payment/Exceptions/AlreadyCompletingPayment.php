<?php namespace Sheba\Payment\Exceptions;

use Throwable;

class AlreadyCompletingPayment extends \Exception
{
    public function __construct($message = "Payment Completion Request is in progress already", $code = 402, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
