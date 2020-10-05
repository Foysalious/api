<?php


namespace Sheba\ExternalPaymentLink\Exceptions;


use Throwable;

class InvalidTransactionIDException extends \Exception
{
    public function __construct($message = "Invalid Transaction ID,Please provide a unique transaction ID", $code = 400, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
