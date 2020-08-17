<?php namespace Sheba\OAuth2;

use Exception;
use Throwable;

class AccountServerAuthenticationError extends Exception
{
    public function __construct($message = "", $code = 401, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Accounts server could not authenticate request.';
        }
        parent::__construct($message, $code, $previous);
    }
}
