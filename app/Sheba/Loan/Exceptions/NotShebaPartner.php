<?php

namespace Sheba\Loan\Exceptions;
use Throwable;

class NotShebaPartner extends LoanException
{
    public function __construct($message = "Not A Sheba Partner", $code = 404, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
