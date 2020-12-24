<?php namespace App\Exceptions\ServiceRequest;


use Exception;
use Throwable;

class MultipleCategoryServiceRequestException extends Exception
{
    public function __construct($message = "Can't add multiple category service in same order.", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}