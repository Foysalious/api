<?php namespace Sheba\Authentication;

use App\Exceptions\ApiValidationException;
use Throwable;

class AuthenticationFailedException extends ApiValidationException
{
    public function __construct($message = "Authentication failed.", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}