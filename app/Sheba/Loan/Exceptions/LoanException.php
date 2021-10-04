<?php namespace Sheba\Loan\Exceptions;

use Exception;
use Sheba\Exceptions\Exceptions\ExceptionForClient;
use Throwable;

class LoanException extends ExceptionForClient
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
