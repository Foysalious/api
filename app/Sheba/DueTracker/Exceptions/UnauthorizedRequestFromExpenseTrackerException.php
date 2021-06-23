<?php namespace Sheba\DueTracker\Exceptions;

use Sheba\Exceptions\Exceptions\ExceptionForClient;
use Throwable;

class UnauthorizedRequestFromExpenseTrackerException extends ExceptionForClient
{
    public function __construct($message = "API KEY MISMATCHED", $code = 401, Throwable $previous = null) { parent::__construct($message, $code, $previous); }
}
