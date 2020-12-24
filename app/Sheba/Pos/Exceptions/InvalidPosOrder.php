<?php

namespace Sheba\Pos\Exceptions;

use Throwable;

class InvalidPosOrder extends \Exception
{
    public function __construct($message = "Invalid Pos Order for this partner", $code = 404, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
