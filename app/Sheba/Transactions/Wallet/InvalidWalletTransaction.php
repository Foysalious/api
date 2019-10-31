<?php namespace Sheba\Transactions\Wallet;


use Throwable;

class InvalidWalletTransaction extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message)) $message = 'Invalid wallet transaction';
        $code = 400;
        parent::__construct($message, $code, $previous);
    }
}
