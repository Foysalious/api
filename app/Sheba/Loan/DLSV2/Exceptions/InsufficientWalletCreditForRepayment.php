<?php namespace App\Sheba\Loan\DLSV2\Exceptions;

use Throwable;

class InsufficientWalletCreditForRepayment extends \Exception
{
    public function __construct($message = "কিছু একটা সমস্যা হয়েছে। টাকা পরিশোধ সফল হয়নি। কিছুক্ষণ পর আবার চেষ্টা করুন।", $code = 403, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}