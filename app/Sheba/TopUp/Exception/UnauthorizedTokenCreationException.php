<?php namespace Sheba\TopUp\Exception;

use App\Exceptions\DoNotReportException;
use Throwable;

class UnauthorizedTokenCreationException extends DoNotReportException
{
    public function __construct($message = "Attempted to create token by unauthorized agent", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
