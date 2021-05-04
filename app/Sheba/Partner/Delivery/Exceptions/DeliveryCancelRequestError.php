<?php namespace App\Sheba\Partner\Delivery\Exceptions;


use App\Exceptions\HttpException;
use Throwable;

class DeliveryCancelRequestError extends HttpException
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Order can not cancelled from current status';
        }
        parent::__construct($message, $code, $previous);
    }
}