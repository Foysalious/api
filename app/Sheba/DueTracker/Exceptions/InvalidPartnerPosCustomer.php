<?php

namespace Sheba\DueTracker\Exceptions;
use Throwable;

class InvalidPartnerPosCustomer extends \Exception
{
    public function __construct($message = "Invalid Partner Pos Customer", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
