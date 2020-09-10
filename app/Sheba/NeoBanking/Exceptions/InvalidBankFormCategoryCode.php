<?php


namespace Sheba\NeoBanking\Exceptions;


use Exception;
use Throwable;

class InvalidBankFormCategoryCode extends Exception
{
    public function __construct($message = "Invalid Bank Form Category Code", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
