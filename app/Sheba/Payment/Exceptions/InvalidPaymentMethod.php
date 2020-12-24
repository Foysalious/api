<?php namespace Sheba\Payment\Exceptions;

use Exception;
use Throwable;

class InvalidPaymentMethod extends Exception
{
    public function __construct($message = "", $code = 404, Throwable $previous = null)
    {
        if ($message == '') $message = 'Invalid Method.';
        parent::__construct($message, $code, $previous);
    }
}
