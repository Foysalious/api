<?php namespace Sheba\EKYC\Exceptions;

use Exception;
use Throwable;

class EkycServerError extends Exception
{
    public function __construct($message = "", $code = 406, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Ekyc server not working as expected.';
        }
        parent::__construct($message, $code, $previous);
    }
}