<?php namespace Sheba\TopUp\Exception;

use Exception;
use Throwable;

class UnknownIpnStatusException extends Exception
{
    public function __construct($message = "Unknown Ipn Status", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
