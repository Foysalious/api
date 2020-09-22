<?php namespace Sheba\Authentication\Exceptions;

use Exception;
use Throwable;

class NotEligibleForApplication extends Exception
{
    public function __construct($portal_name, $code = 403, Throwable $previous = null)
    {
        $message = "You are not eligible for using $portal_name.";
        parent::__construct($message, $code, $previous);
    }
}
