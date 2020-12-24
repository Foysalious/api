<?php namespace Sheba\Authentication\Exceptions;

use Exception;
use Throwable;

class ProfileBlacklisted extends Exception
{
    public function __construct($code = 403, Throwable $previous = null)
    {
        $message = "Your profile has been blacklisted.";
        parent::__construct($message, $code, $previous);
    }
}
