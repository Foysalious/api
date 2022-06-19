<?php

namespace Sheba\Payment\Methods\Upay\Exceptions;

use Exception;
use Throwable;

class UpayPaymentException extends Exception
{
    public function __construct($message = "Upay Payment Exception", $code = 500, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}