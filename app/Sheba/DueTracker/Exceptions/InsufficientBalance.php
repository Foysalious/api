<?php namespace App\Sheba\DueTracker\Exceptions;

use Sheba\Exceptions\Exceptions\ExceptionForClient;
use Throwable;

class InsufficientBalance extends ExceptionForClient
{
    public function __construct($message = "Insufficient Balance", $code = 402, Throwable $previous = null) { parent::__construct($message, $code, $previous); }
}
