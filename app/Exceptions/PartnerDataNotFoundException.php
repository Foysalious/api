<?php namespace App\Exceptions;

use Sheba\Exceptions\Exceptions\ExceptionForClient;
use Throwable;

class PartnerDataNotFoundException extends ExceptionForClient
{
    public function __construct($message = "Partner Data not available", $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}