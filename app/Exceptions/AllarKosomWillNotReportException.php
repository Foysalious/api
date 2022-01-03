<?php namespace App\Exceptions;

use Exception;
use Throwable;

class AllarKosomWillNotReportException extends DoNotReportException
{
    public function __construct($message = 'Bad Request', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
