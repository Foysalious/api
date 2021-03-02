<?php

namespace Sheba\NeoBanking\Exceptions;

use Throwable;

class AccountCreateException extends NeoBankingException
{
    public function __construct($message = "Neo Banking Exception", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
