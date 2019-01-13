<?php
/**
 * Created by PhpStorm.
 * User: tonmoy
 * Date: 1/13/19
 * Time: 10:34 AM
 */

namespace App\Exceptions;


class ModeratorDistanceExceedException extends \Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == '') {
            $message = 'You have to be at least ' . config('constants.MODERATOR_DISTANCE_THRESHOLD') . ' m near the partners location to accept or reject verification request';
        }
        parent::__construct($message, $code, $previous);

    }

    protected function render(Exception $e)
    {
        parent::render($e);
    }
}