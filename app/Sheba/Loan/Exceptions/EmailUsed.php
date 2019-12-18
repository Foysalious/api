<?php


namespace Sheba\Loan\Exceptions;


use Throwable;

class EmailUsed extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message)) $message = "Email used by another user";
        parent::__construct($message, $code, $previous);
    }
}
