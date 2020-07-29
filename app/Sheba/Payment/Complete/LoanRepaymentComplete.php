<?php


namespace Sheba\Payment\Complete;


use App\Models\PartnerBankLoan;
use App\Models\Payable;
use App\Sheba\Loan\DLSV2\LoanClaim;
use App\Sheba\Loan\DLSV2\Repayment;

class LoanRepaymentComplete extends PaymentComplete
{

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }

    public function complete()
    {
        /** @var Payable $payable */
        $payable = $this->payment->payable;
        /** @var PartnerBankLoan $loan */
        $loan      = $payable->getPayableType();
        $lastClaim = (new LoanClaim())->setLoan($loan->id)->lastClaim();
        (new Repayment())->setLoan($loan->id)
                         ->setAmount($payable->amount)
                         ->setClaim($lastClaim->id)
                         ->storeDebit("Online, Payment ID : {$this->payment->id}");
    }
}
