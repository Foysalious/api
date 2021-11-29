<?php namespace Sheba\PushNotification\Exceptions;


use App\Exceptions\HttpException;
use Throwable;

class PushNotificationServerError extends HttpException
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Push notification service server not working as expected.';
        }
        parent::__construct($message, $code, $previous);

    }
}
