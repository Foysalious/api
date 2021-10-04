<?php namespace Sheba\TPProxy;

use Exception;
use Throwable;

class TPProxyServerError extends Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'TP proxy server not working as expected.';
        } else {
            $message = "TP proxy server not working: $message";
        }
        parent::__construct($message, $code, $previous);
    }
}
