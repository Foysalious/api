<?php

namespace Sheba\NeoBanking\Exceptions;

use Throwable;

class InvalidPartnerInformationException extends NeoBankingException
{
    public function __construct($message = "Invalid Partner Information", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}