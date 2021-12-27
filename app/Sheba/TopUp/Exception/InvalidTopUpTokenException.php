<?php namespace Sheba\TopUp\Exception;

use App\Exceptions\DoNotReportException;
use Throwable;

class InvalidTopUpTokenException extends DoNotReportException
{
    public function __construct($message = "Not a valid token", $code = 406, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
