<?php namespace Sheba\Subscription\Partner\Access\Exceptions;

use App\Exceptions\DoNotReportException;
use Exception;
use Throwable;

class AccessRestrictedExceptionForPackage extends DoNotReportException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = $message == "" ? "Your package doesn't have access to this feature please upgrade" : $message;
        $code = 403;
        parent::__construct($message, $code, $previous);
    }
}
