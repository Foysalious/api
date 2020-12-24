<?php namespace Sheba\ExpenseTracker\Exceptions;

use Exception;
use Throwable;

class InvalidHeadException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Invalid Head assigned for entry.';
        }
        parent::__construct($message, $code, $previous);
    }
}
