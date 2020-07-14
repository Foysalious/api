<?php namespace App\Exceptions;

use Exception;
use Throwable;

class InvalidModeratorException extends Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == '') {
            $message = 'Invalid Moderator';
        }

        parent::__construct($message, $code, $previous);
    }
}
