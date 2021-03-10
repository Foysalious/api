<?php

namespace Sheba\NeoBanking\Exceptions;

use Throwable;

class AccountNotFoundException extends NeoBankingException
{
    public function __construct($message = "Neo Banking account not found", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
