<?php namespace Sheba\Subscription\Exceptions;


use Throwable;

class InvalidPreviousSubscriptionRules extends \Exception
{
    public function __construct($message = "Previous Subscription rules are not set or invalid", $code = 400, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
