<?php

namespace Sheba\AccountingEntry\Exceptions;

use App\Exceptions\DoNotReportException;

use Throwable;

class MigratedToAccountingException extends DoNotReportException
{
    public function __construct($message = "", $code = 401, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Sorry! User already migrated to accounting.';
        }
        parent::__construct($message, $code, $previous);

    }
}