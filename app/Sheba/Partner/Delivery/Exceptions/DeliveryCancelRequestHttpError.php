<?php namespace App\Sheba\Partner\Delivery\Exceptions;

use App\Exceptions\HttpException;

class DeliveryCancelRequestHttpError extends HttpException
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'sDelivery server not working as expected.';
        }
        parent::__construct($message, $code, $previous);
    }
}