<?php namespace Sheba\TopUp\Exception;

use Throwable;

class PayStationNotWorkingException extends \Exception
{
    public function __construct($message = "Pay Station Not Working", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
