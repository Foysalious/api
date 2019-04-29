<?php namespace Sheba\Pos\Log\Exceptions;

use Exception;
use Throwable;

class UnsupportedType extends Exception
{
    public function __construct($type, $code = 402, Throwable $previous = null)
    {
        $message = "$type is not supported.";
        parent::__construct($message, $code, $previous);

    }
}