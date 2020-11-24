<?php


namespace App\Exceptions;


class InvalidAddressException extends ApiValidationException
{
    public function __construct($message = 'The Address is invalid.', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}