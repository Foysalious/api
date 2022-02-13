<?php

namespace App\Sheba\ResellerPayment\Exceptions;

use Throwable;

class MORServiceServerError extends \Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'MOR Service server not working as expected.';
        }
        parent::__construct($message, $code, $previous);

    }

}