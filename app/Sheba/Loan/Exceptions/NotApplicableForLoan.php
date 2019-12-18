<?php

namespace Sheba\Loan\Exceptions;

use Throwable;

class NotApplicableForLoan extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message))
            $message = "You are not applicable for loan,Please fill out all the fields";
        parent::__construct($message, $code, $previous);
    }
}
