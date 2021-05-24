<?php namespace App\Exceptions\Pos\SMS;


use App\Exceptions\DoNotReportException;
use Throwable;

class InsufficientBalanceException extends DoNotReportException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = $message == "" ? "You don't have sufficient balance to send this sms" : $message;
        $code = 403;
        parent::__construct($message, $code, $previous);
    }

}