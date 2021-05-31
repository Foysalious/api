<?php namespace Sheba\Payment\Methods\Nagad\Exception;

use Exception;
use Throwable;

class EncryptionFailed extends Exception
{
    public function __construct($message = "Failed to encrypt data", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
