<?php

namespace Sheba\Loan\Exceptions;

use Throwable;

class EmailUsed extends LoanException
{
    public function __construct($message = "Email used by another user", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
