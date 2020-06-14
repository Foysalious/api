<?php namespace App\Exceptions;

use Exception;
use Throwable;

class MailgunClientException extends Exception
{
    public function __construct($message = "", $code = 503, Throwable $previous = null)
    {
        if (!$message || $message == '') {
            $message = 'An error occurred when sending email. Please try later.';
        }
        parent::__construct($message, $code, $previous);
    }
}
