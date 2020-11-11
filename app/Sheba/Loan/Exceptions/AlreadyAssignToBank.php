<?php

namespace Sheba\Loan\Exceptions;

use Throwable;

class AlreadyAssignToBank extends LoanException
{
    public function __construct($message = "This request is already assign to a bank", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
