<?php namespace Sheba\Payment\Methods\OkWallet\Exception;

use Throwable;

class FailedToInitiateException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message)) $message = "Failed to initiate ok wallet";
        parent::__construct($message, $code, $previous);
    }
}
