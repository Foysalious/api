<?php namespace Sheba\Loan\Exceptions;

use Throwable;

class NotAllowedToAccess extends LoanException
{
    public function __construct($message = "You are not allowed to access this request", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
