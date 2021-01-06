<?php


namespace Sheba\TopUp\Exception;


use App\Exceptions\DoNotThrowException;
use Throwable;

class ResetRememberTokenException extends DoNotThrowException
{
    public function __construct($message = "User logged out due to wrong PIN count reached 3.", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}