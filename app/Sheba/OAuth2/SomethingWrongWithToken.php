<?php namespace Sheba\OAuth2;

use Sheba\Exceptions\Exceptions\ExceptionForClient;
use Throwable;

class SomethingWrongWithToken extends ExceptionForClient
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Something is wrong with the token generated form accounts server.';
        }
        parent::__construct($message, $code, $previous);
    }
}
