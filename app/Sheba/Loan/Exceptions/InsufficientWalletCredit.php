<?php namespace Sheba\Loan\Exceptions;


use Throwable;

class InsufficientWalletCredit extends LoanException
{
    public function __construct($message = "আপনার ওয়ালেট এ যথেষ্ট পরিমাণ ব্যলান্স নেই দয়া করে রিচার্জ করুন", $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
