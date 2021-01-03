<?php namespace Sheba\Pos\Exceptions;


use App\Exceptions\ApiValidationException;
use Throwable;

class PosServiceNotFoundException extends ApiValidationException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = $message == "" ? "Service not found with provided ID" : $message;
        $code = 400;
        parent::__construct($message, $code, $previous);
    }

}