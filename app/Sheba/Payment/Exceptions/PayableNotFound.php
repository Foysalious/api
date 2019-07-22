<?php namespace Sheba\Payment\Exceptions;

use Exception;
use Throwable;

class PayableNotFound extends Exception
{
    public function __construct($message = "", $code = 404, Throwable $previous = null)
    {
        if ($message == '') $message = 'Requested payable is not found';
        parent::__construct($message, $code, $previous);
    }
}
