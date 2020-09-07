<?php


namespace Sheba\NeoBanking\Exceptions;


use Exception;
use Throwable;

class InvalidListInsertion extends Exception
{
    public function __construct($message = "Trying to insert invalid list item", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
