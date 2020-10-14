<?php


namespace Sheba\NeoBanking\Exceptions;


use Throwable;

class InvalidBankCode extends NeoBankingException
{
    public function __construct($message = "Invalid Bank Code Provided", $code = 500, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
