<?php

namespace Sheba\Transactions\Wallet;

use Exception;
use Throwable;

class WalletDebitForbiddenException extends Exception
{
    public function __construct($message = "", $code = 403, Throwable $previous = null)
    {
        if (empty($message)) $message = 'Invalid wallet transaction';
        parent::__construct($message, $code, $previous);
    }
}