<?php namespace App\Exceptions;

use Exception;
use Throwable;

class ApiValidationException extends Exception
{
    public function __construct($message = "Something went wrong", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
