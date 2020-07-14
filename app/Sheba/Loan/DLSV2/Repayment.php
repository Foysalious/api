<?php namespace App\Sheba\Loan\DLSV2;

use Sheba\Dal\LoanPayment\Model;
use Sheba\ModificationFields;

class Repayment
{
    use ModificationFields;
    private $loanId;
    private $repayment;
    public function __construct()
    {
    }

    public function setLoan($loan_id)
    {
        $this->loanId = $loan_id;
        return $this;
    }

    public function isEligibleForClaim()
    {
        $total_debit = Model::where('loan_id',$this->loanId)->sum('debit');
        $total_credit = Model::where('loan_id',$this->loanId)->sum('credit');

        return $total_credit == $total_debit;
    }

    public function getDue()
    {
        $total_debit = Model::where('loan_id',$this->loanId)->sum('debit');
        $total_credit = Model::where('loan_id',$this->loanId)->sum('credit');

        return $total_credit - $total_debit;
    }

    public function get($claim_id)
    {


    }

    public function storeDebit($data)
    {
        $this->repayment = new Model($this->withCreateModificationField($data));
        $this->repayment->save();
    }

}