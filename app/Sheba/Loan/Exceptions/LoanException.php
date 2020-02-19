<?php namespace Sheba\Loan\Exceptions;

use Exception;
use Throwable;

class LoanException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
