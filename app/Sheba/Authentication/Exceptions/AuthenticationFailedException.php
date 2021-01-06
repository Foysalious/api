<?php namespace Sheba\Authentication\Exceptions;

use App\Exceptions\DoNotThrowException;
use Throwable;

class AuthenticationFailedException extends DoNotThrowException
{
    public function __construct($message = "Authentication failed.", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
