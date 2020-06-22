<?php


namespace Sheba\Subscription\Exceptions;


use Throwable;

class AlreadyRunningSubscriptionRequestException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
