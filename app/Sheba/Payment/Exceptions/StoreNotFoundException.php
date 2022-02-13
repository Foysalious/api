<?php

namespace Sheba\Payment\Exceptions;

use Throwable;

class StoreNotFoundException extends \Exception
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "No store configuration found for this partner";
        parent::__construct($message, $code, $previous);
    }
}