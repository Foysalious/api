<?php


namespace Sheba\NeoBanking\Exceptions;


use Throwable;

class UnauthorizedRequestFromSBSException extends \Exception
{
    public function __construct($message = "Api access key mismatched", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}