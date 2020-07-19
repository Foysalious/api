<?php namespace App\Sheba\Loan\DLSV2;

use Carbon\Carbon;
use Sheba\Dal\LoanPayment\Model as RepaymentModel;
use Sheba\Dal\LoanPayment\EloquentImplementation as RepaymentRepo;
use Sheba\ModificationFields;

class Repayment
{
    use ModificationFields;
    private $loanId;
    private $repayment;
    private $claimId;
    private $amount;
    private $repaymentRepo;

    public function __construct()
    {
    }

    /**
     * @param $loan_id
     * @return $this
     */
    public function setLoan($loan_id)
    {
        $this->loanId = $loan_id;
        return $this;
    }

    /**
     * @param $claim_id
     * @return $this
     */
    public function setClaim($claim_id)
    {
        $this->claimId = $claim_id;
        return $this;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount= $amount;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getDue()
    {
        $total_debit = (new RepaymentRepo(new RepaymentModel()))->getDebitByClaimId($this->claimId);
        $total_credit = (new RepaymentRepo(new RepaymentModel()))->getCreditByClaimId($this->claimId);;

        return $total_credit - $total_debit;
    }

    public function get($claim_id)
    {


    }

    public function storeDebit($data)
    {

    }

    /**
     * @return bool
     */
    public function storeCreditPaymentEntry()
    {
        $data = [
            'loan_id' => $this->loanId,
            'loan_claim_request_id' => $this->claimId,
            'credit' => $this->amount,
            'debit' => 0,
            'type' => '',
            'log' => 'amount transferred to robi loan wallet',
            'defaulter_date' => Carbon::now()->addDays(30)
        ];
        $this->repayment = new RepaymentModel($this->withCreateModificationField($data));
        return $this->repayment->save();
    }


}