<?php namespace Sheba\TopUp\Exception;

use Throwable;

class GatewayTimeout extends \Exception
{
    public function __construct($message = "Gateway timeout", $code = 502, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
