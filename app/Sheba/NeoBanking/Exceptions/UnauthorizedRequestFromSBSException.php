<?php


namespace Sheba\NeoBanking\Exceptions;


use Throwable;

class UnauthorizedRequestFromSBSException extends NeoBankingException
{
    public function __construct($message = "Api access key mismatched", $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}