<?php namespace App\Exceptions;

use Exception;
use Throwable;

class ModeratorDistanceExceedException extends Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == '') {
            $message = 'You have to be at least ' . constants('MODERATOR_DISTANCE_THRESHOLD');
            $message .= ' m near the partners location to accept or reject verification request';
        }
        parent::__construct($message, $code, $previous);
    }
}
