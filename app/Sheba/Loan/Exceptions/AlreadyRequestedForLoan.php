<?php

namespace Sheba\Loan\Exceptions;

use Throwable;

class AlreadyRequestedForLoan extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message))
            $message = 'Already requested for loan';
        parent::__construct($message, $code, $previous);
    }
}
