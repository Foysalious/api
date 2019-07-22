<?php namespace Sheba\Payment\Exceptions;

use Exception;
use Throwable;

class PaymentLinkInactive extends Exception
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        if ($message == "") $message = "This PaymentLink is inactive";
        parent::__construct($message, $code, $previous);
    }
}
