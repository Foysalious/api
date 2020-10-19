<?php


namespace Sheba\NeoBanking\Exceptions;


use Throwable;

class InvalidListInsertion extends NeoBankingException
{
    public function __construct($message = "Trying to insert invalid list item", $code = 400, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
