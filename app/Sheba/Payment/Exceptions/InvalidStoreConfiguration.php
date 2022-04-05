<?php

namespace Sheba\Payment\Exceptions;

use Exception;
use Throwable;

class InvalidStoreConfiguration extends Exception
{
    public function __construct($message = "Invalid Store Configuration", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
