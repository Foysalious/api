<?php

namespace Sheba\Transactions\Wallet;

use App\Exceptions\DoNotReportException;
use Throwable;

class WalletDebitForbiddenException extends DoNotReportException
{
    public function __construct($message = "", $code = 406, Throwable $previous = null)
    {
        if (empty($message)) $message = 'Invalid wallet transaction';
        parent::__construct($message, $code, $previous);
    }
}