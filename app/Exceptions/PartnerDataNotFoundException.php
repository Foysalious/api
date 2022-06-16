<?php namespace App\Exceptions;

use Exception;
use Throwable;

class PartnerDataNotFoundException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Partner Data not available";
        $code = 403;
        parent::__construct($message, $code, $previous);
    }
}