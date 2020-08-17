<?php namespace Sheba\OAuth2;

use Exception;
use Throwable;

class SomethingWrongWithToken extends Exception
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Something is wrong with the token generated form accounts server.';
        }
        parent::__construct($message, $code, $previous);
    }
}
