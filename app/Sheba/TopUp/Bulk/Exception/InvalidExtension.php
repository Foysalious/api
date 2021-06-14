<?php namespace Sheba\TopUp\Bulk\Exception;

use App\Exceptions\DoNotReportException;
use Throwable;

class InvalidExtension extends DoNotReportException
{
    public function __construct($message = 'File type not support', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
