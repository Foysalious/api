<?php namespace Sheba\Payment\Methods\Nagad\Exception;

use Exception;
use Throwable;

class InvalidOrderId extends Exception
{
    public function __construct($message = "Invalid Order ID", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
