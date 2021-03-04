<?php namespace Sheba\AccessToken\Exception;

use App\Exceptions\DoNotReportException;
use Throwable;

class AccessTokenNotValidException extends DoNotReportException
{
    public function __construct($message = "Your session has expired. Try Login", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
