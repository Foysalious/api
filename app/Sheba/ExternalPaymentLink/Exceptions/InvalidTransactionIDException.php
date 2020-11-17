<?php


namespace Sheba\ExternalPaymentLink\Exceptions;


use Throwable;

class InvalidTransactionIDException extends ExternalPaymentLinkException
{
    public function __construct($message = "Invalid Transaction ID,Please provide a unique transaction ID", $code = 505, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
