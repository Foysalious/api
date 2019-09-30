<?php namespace Sheba\Logistics\Exceptions;

use Exception;
use Throwable;

class LogisticServerError extends Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Logistic server not working as expected.';
        }
        parent::__construct($message, $code, $previous);

    }
}