<?php

namespace Sheba\QRPayment\Exceptions;

use Throwable;

class FinancialInformationNotFoundException extends QRException
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if ($message == "") $message = "Financial Information Not Found";
        parent::__construct($message, $code, $previous);
    }
}