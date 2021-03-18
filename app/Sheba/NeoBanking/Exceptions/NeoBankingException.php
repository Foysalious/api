<?php


namespace Sheba\NeoBanking\Exceptions;


use Throwable;

class NeoBankingException extends \Exception
{
    public function __construct($message = "Neo Banking Exception", $code = 400, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
