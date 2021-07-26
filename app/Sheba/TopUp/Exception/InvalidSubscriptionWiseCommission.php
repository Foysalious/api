<?php namespace Sheba\TopUp\Exception;

use Throwable;

class InvalidSubscriptionWiseCommission extends \Exception
{
    public function __construct($message = "Invalid subscription wise top-up commission", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
