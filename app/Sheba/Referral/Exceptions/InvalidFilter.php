<?php

namespace Sheba\Referral\Exceptions;
use Throwable;

class InvalidFilter extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
