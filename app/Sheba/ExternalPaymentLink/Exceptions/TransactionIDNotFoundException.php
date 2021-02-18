<?php


namespace Sheba\ExternalPaymentLink\Exceptions;


use Throwable;

class TransactionIDNotFoundException extends ExternalPaymentLinkException
{
    public function __construct($message = "The given transaction ID not found", $code = 404, Throwable $previous = null) { parent::__construct($message, $code, $previous); }
}
