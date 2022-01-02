<?php

namespace Sheba\Payment\Exceptions;

use Throwable;

class InvalidConfigurationException extends \Exception
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "Invalid credentials";
        parent::__construct($message, $code, $previous);
    }
}