<?php namespace Sheba\TopUp\Exception;

use App\Exceptions\DoNotReportException;
use Throwable;

class ResetRememberTokenException extends DoNotReportException
{
    public function __construct($message = "User logged out due to wrong PIN count reached 3.", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
