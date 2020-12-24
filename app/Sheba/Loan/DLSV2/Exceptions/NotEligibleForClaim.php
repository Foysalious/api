<?php namespace App\Sheba\Loan\DLSV2\Exceptions;


use Sheba\Loan\Exceptions\LoanException;
use Throwable;

class NotEligibleForClaim extends LoanException
{
    public function __construct($message = "কিছু একটা সমস্যা হয়েছে যার কারণে আপনার আবেদনটি জমা দেয়া সম্ভব হয়নি", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}