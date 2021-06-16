<?php namespace App\Sheba\PosOrderService\Exceptions;


use App\Exceptions\HttpException;
use Throwable;

class PosOrderServiceServerError extends HttpException
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Inventory Service server not working as expected.';
        }
        parent::__construct($message, $code, $previous);

    }

}