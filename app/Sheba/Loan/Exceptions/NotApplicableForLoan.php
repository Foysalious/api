<?php namespace Sheba\Loan\Exceptions;

use Throwable;

class NotApplicableForLoan extends LoanException
{
    public function __construct($message = "You are not applicable for loan, Please fill out all the fields", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
