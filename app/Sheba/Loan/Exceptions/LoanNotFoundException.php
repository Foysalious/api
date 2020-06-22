<?php namespace App\Sheba\Loan\Exceptions;

use Sheba\Loan\Exceptions\LoanException;
use Throwable;

class LoanNotFoundException extends LoanException
{
    public function __construct($message = "Not Found", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
