<?php namespace Sheba\Loan\Exceptions;

use Throwable;

class InvalidFileName extends LoanException
{
    public function __construct($message = "Invalid File Name", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
