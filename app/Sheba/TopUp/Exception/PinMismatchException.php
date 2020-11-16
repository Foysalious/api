<?php


namespace Sheba\TopUp\Exception;


use Throwable;

class PinMismatchException extends TopUpExceptions
{
    public function __construct($message = "Pin Mismatch", $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}