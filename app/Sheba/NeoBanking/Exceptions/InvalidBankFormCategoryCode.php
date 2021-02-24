<?php


namespace Sheba\NeoBanking\Exceptions;


use Throwable;

class InvalidBankFormCategoryCode extends NeoBankingException
{
    public function __construct($message = "Invalid Bank Form Category Code", $code = 400, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
