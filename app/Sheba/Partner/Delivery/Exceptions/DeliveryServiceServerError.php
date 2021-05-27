<?php namespace App\Sheba\Partner\Delivery\Exceptions;


use App\Exceptions\DoNotReportException;
use App\Exceptions\HttpException;
use Throwable;

class DeliveryServiceServerError extends DoNotReportException
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'sDelivery server not working as expected.';
        }
        parent::__construct($message, $code, $previous);
    }
}
