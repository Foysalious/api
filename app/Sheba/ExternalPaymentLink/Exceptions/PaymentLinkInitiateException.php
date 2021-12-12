<?php

namespace Sheba\ExternalPaymentLink\Exceptions;

use Throwable;

class PaymentLinkInitiateException extends ExternalPaymentLinkException
{
    public function __construct($message = "Failed to initiate Payment Link", $code = 507, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
