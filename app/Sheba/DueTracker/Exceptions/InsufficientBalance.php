<?php


namespace App\Sheba\DueTracker\Exceptions;

use Throwable;


class InsufficientBalance extends \Exception
{
    public function __construct($message = "Insufficient Balance", $code = 401, Throwable $previous = null) { parent::__construct($message, $code, $previous); }
}