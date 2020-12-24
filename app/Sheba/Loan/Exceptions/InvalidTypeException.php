<?php


namespace Sheba\Loan\Exceptions;


use Exception;
use Sheba\Dal\PartnerBankLoan\LoanTypes;
use Throwable;

class InvalidTypeException extends Exception
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        $message = !empty($message) ? $message : "Invalid Type!! Type must be in " . implode(',', LoanTypes::get());
        parent::__construct($message, $code, $previous);
    }

}
