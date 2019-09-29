<?php namespace Sheba\Reports\Exceptions;

use Exception;
use Throwable;

class NotAssociativeArray extends Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        $message = $message ?: 'Data must be an associative array.';
        parent::__construct($message, $code, $previous);
    }
}