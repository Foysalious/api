<?php namespace App\Sheba\MTB\Exceptions;

use App\Exceptions\HttpException;
use Throwable;

class MtbServiceServerError extends HttpException
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'MTB Service server not working as expected.';
        }
        parent::__construct($message, $code, $previous);
    }
}
