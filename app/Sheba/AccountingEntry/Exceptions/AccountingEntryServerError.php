<?php namespace Sheba\AccountingEntry\Exceptions;

use Exception;
use Throwable;

class AccountingEntryServerError extends Exception
{
    public function __construct($message = "", $code = 406, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Accounting Entry server not working as expected.';
        }
        parent::__construct($message, $code, $previous);

    }
}
