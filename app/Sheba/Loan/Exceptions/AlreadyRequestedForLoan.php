<?php

namespace Sheba\Loan\Exceptions;

use Throwable;

class AlreadyRequestedForLoan extends LoanException
{
    public function __construct($message = 'Already requested for loan', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
