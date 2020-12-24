<?php namespace Sheba\OAuth2;

use Exception;
use Throwable;

class AccountServerNotWorking extends Exception
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Accounts server not working as expected.';
        }
        parent::__construct($message, $code, $previous);
    }
}
