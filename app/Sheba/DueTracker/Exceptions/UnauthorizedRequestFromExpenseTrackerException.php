<?php

namespace Sheba\DueTracker\Exceptions;
use Throwable;

class UnauthorizedRequestFromExpenseTrackerException extends \Exception
{
    public function __construct($message = "API KEY MISMATCHED", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }
}
