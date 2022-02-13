<?php namespace App\Exceptions;


use Throwable;

class DataMismatchException extends DoNotReportException
{
    public function __construct($message = 'Partner has data mismatch issue', $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}