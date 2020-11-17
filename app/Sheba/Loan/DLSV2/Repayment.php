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
    private $type;

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
        $this->amount = $amount;
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

    public function storeDebit($type)
    {
        $data = [
            'loan_id' => $this->loanId,
            'loan_claim_request_id' => $this->claimId,
            'credit' => 0,
            'debit' => $this->amount,
            'type' => $type,
            'log' => 'টাকা জমা দেয়া হয়েছে।',
        ];
        $this->repayment = new RepaymentModel($this->withCreateModificationField($data));
        return $this->repayment->save();
    }

    public function setDefaulterDuration($defaulter_duration)
    {
        $this->defaulterDuration = $defaulter_duration;
        return $this;
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
            'log' => 'ক্রেডিট লিমিট থেকে গ্রহন করা হয়েছে'
        ];
        $this->repayment = new RepaymentModel($this->withCreateModificationField($data));
        return $this->repayment->save();
    }

    public function storeCreditPaymentEntryForAnnualFee()
    {
        $data = [
            'loan_id' => $this->loanId,
            'loan_claim_request_id' => $this->claimId,
            'credit' => $this->amount,
            'debit' => 0,
            'type' => '',
            'log' => 'বার্ষিক ফি বাবদ চার্জ করা হয়েছে',
        ];
        $this->repayment = new RepaymentModel($this->withCreateModificationField($data));
        return $this->repayment->save();
    }

    public function storeCreditPaymentEntryForClaimTransactionFee()
    {
        $data = [
            'loan_id' => $this->loanId,
            'loan_claim_request_id' => $this->claimId,
            'credit' => $this->amount,
            'debit' => 0,
            'type' => '',
            'log' => 'দাবির ফি বাবদ চার্জ করা হয়েছে',
        ];
        $this->repayment = new RepaymentModel($this->withCreateModificationField($data));
        return $this->repayment->save();
    }

    public function storeCreditShebaInterestFee()
    {
        $data = [
            'loan_id' => $this->loanId,
            'loan_claim_request_id' => $this->claimId,
            'credit' => $this->amount,
            'debit' => 0,
            'type' => '',
            'log' => 'সেবা টপ-আপ ফ্যাসিলিটি ফি বাবদ চার্জ করা হয়েছে',
        ];
        $this->repayment = new RepaymentModel($this->withCreateModificationField($data));
        return $this->repayment->save();
    }


    public function getByYearAndMonth($loan_id, $year, $month)
    {
        return (new RepaymentRepo(new RepaymentModel()))->getByYearAndMonth($loan_id,$year,$month);
    }


    public function getAll($loan_id)
    {
        return (new RepaymentRepo(new RepaymentModel()))->getByLoanID($loan_id);

    }


    public function getRecent($loan_id)
    {
        return (new RepaymentRepo(new RepaymentModel()))->getRecent($loan_id);
    }

    public function repaymentFromWallet(){

        $data = [
            'loan_id' => $this->loanId,
            'loan_claim_request_id' => $this->claimId,
            'credit' => 0,
            'debit' => $this->amount,
            'type' => 'By Sheba',
            'log' => 'টাকা জমা দেয়া হয়েছে।',
        ];
        $this->repayment = new RepaymentModel($this->withCreateModificationField($data));
        return $this->repayment->save();
    }

    private function balanceCheck()
    {


    }


}
