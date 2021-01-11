<?php namespace Sheba\Authentication\Exceptions;

use App\Exceptions\DoNotReportException;
use Throwable;

class AuthenticationFailedException extends DoNotReportException
{
    public function __construct($message = "Authentication failed.", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
