<?php

namespace Sheba\Pos\Exceptions;
use Throwable;

class PosExpenseCanNotBeDeleted extends \Exception
{
    public function __construct($message = "Pos order expense can not be deleted", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
