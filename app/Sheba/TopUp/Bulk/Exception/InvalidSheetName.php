<?php namespace Sheba\TopUp\Bulk\Exception;

use App\Exceptions\DoNotReportException;
use Throwable;

class InvalidSheetName extends DoNotReportException
{
    public function __construct($message = 'The sheet name used in the excel file is incorrect. Please download the sample excel file for reference.', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
