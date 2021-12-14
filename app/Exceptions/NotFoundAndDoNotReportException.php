<?php namespace App\Exceptions;

use Throwable;

class NotFoundAndDoNotReportException extends HttpException
{
    public function __construct($message = "", $code = 404, Throwable $previous = null)
    {
        $message = $message == "" ? "Not Found" : $message;
        $code = 404;
        parent::__construct($message, $code, $previous);
    }
}