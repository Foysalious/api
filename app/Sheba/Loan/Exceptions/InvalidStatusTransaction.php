<?php

namespace Sheba\Loan\Exceptions;

use Throwable;

class InvalidStatusTransaction extends LoanException
{
    public function __construct($message = "You can not transact to this status from here", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
