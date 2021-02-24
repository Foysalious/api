<?php namespace App\Exceptions;


use Exception;
use Throwable;

class ApiException extends Exception
{
    public function __construct($message = 'Bad Request', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}