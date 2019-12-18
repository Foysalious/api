<?php

namespace Sheba\Loan\Exceptions;

use Throwable;

class AlreadyAssignToBank extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message))
            $message = "This request is already assign to a bank";
        parent::__construct($message, $code, $previous);
    }
}
