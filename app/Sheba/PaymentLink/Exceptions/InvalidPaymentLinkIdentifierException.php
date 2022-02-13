<?php namespace Sheba\PaymentLink\Exceptions;

use Throwable;

class InvalidPaymentLinkIdentifierException extends \Exception
{
    public function __construct($message = "Payment link not found using this identifier", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}