<?php namespace Sheba\TPProxy;

use Exception;
use Throwable;

class TPProxyServerTimeout extends TPProxyServerError
{
    public function __construct($message = "TP proxy server timeout.", $code = 502, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
