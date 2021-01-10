<?php namespace Sheba\Payment\Exceptions;

use App\Exceptions\DoNotReportException;
use Throwable;

class InitiateFailedException extends DoNotReportException
{
    public function __construct($message = 'Payment initiation failed!', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
