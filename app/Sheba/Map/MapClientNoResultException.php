<?php namespace Sheba\Map;

use App\Exceptions\DoNotReportException;
use Throwable;

class MapClientNoResultException extends DoNotReportException
{
    public function __construct($message = 'Invalid delivery address! Please check.', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
