<?php namespace Sheba\EKYC\Exceptions;

use Exception;
use Throwable;

class EKycException extends Exception
{
    public function __construct($message = "EKyc Exception", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}