<?php namespace Sheba\Payment\Exceptions;

use App\Exceptions\DoNotThrowException;
use Throwable;

class InitiateFailedException extends DoNotThrowException
{
    public function __construct($message = 'Payment initiation failed!', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
