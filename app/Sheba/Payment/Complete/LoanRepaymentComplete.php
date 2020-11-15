<?php namespace Sheba\Payment\Complete;


use App\Models\PartnerBankLoan;
use App\Models\Payable;
use App\Sheba\Loan\DLSV2\LoanClaim;
use App\Sheba\Loan\DLSV2\Repayment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

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
        try {
            DB::transaction(function () use ($payable) {
                $loan      = $payable->getPayableType();
                $lastClaim = (new LoanClaim())->setLoan($loan->id)->lastClaim();
                (new Repayment())->setLoan($loan->id)
                                 ->setAmount($payable->amount)
                                 ->setClaim($lastClaim ? $lastClaim->id : null)
                                 ->storeDebit("Online, Payment ID : {$this->payment->id}");
                $this->completePayment();
            });
        } catch (QueryException $e) {
            $this->payment->transaction_details = $e->getMessage();
            $this->failPayment();
        }
        return $this->payment;
    }
}
