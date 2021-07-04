<?php namespace Sheba\DueTracker\Exceptions;

use Sheba\Exceptions\Exceptions\ExceptionForClient;
use Throwable;

class InvalidPartnerPosCustomer extends ExceptionForClient
{
    public function __construct($message = "Invalid Partner Pos Customer", $code = 403, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
