<?php namespace App\Sheba\PosCustomerService\Exceptions;

use App\Exceptions\HttpException;
use Throwable;


class SmanagerUserServiceServerError extends HttpException
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Smanager User Service server not working as expected.';
        }
        parent::__construct($message, $code, $previous);

    }

}