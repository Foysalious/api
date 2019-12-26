<?php

namespace Sheba\Loan\Exceptions;

use Throwable;

class NotAllowedToAccess extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message))
            $message = "You are not allowed to access this request";
        parent::__construct($message, $code, $previous);
    }
}
