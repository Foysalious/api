<?php


namespace Sheba\NeoBanking\Exceptions;


use Exception;
use Throwable;

class InvalidBankCode extends Exception
{
    public function __construct($message = "Invalid Bank Code Provided", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
