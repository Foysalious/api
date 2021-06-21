<?php namespace Sheba\PaymentLink\Exceptions;

use Throwable;

class InvalidGatewayChargesException extends \Exception
{
    public function __construct($message = "Invalid payment gateway exception", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}