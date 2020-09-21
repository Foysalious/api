<?php namespace App\Exceptions;

use Throwable;

class HyperLocationNotFoundException extends ApiValidationException
{
    public function __construct($message = 'Your are out of service area.', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
