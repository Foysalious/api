<?php


namespace Sheba\ExternalPaymentLink\Exceptions;


use Throwable;

class InvalidEmiMonthException extends ExternalPaymentLinkException
{
    public function __construct($message = "Invalid EMI Month given", $code = 506, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
