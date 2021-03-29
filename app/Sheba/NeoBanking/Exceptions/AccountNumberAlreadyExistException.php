<?php

namespace Sheba\NeoBanking\Exceptions;

use Throwable;

class AccountNumberAlreadyExistException extends NeoBankingException
{
    public function __construct($message = "Neo Banking account number already exist", $code = 1, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
