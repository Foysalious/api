<?php namespace Sheba\TopUp\Exception;

use Throwable;

class TopUpExceptions extends \Exception
{
    public function __construct($message = "Exception in topup", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
