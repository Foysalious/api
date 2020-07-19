<?php namespace App\Sheba\Loan\DLSV2;


use Sheba\Dal\LoanClaimRequest\Model as LoanClaimModel;
use Sheba\Dal\LoanClaimRequest\EloquentImplementation as LoanClaimRepo;
use Sheba\ModificationFields;

class LoanClaim
{

    use ModificationFields;
    private $loanClaimRequest;
    private $loanId;
    private $claimId;

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
     * @return mixed
     */
    public function lastClaim()
    {
        return LoanClaimModel::lastClaim($this->loanId)->first();
    }

    /**
     * @param $to
     */
    public function updateStatus($to)
    {

        $claim = (new LoanClaimRepo(new LoanClaimModel()))->find($this->claimId);
        $claim->status = $to;
        $claim->log = $this->getLog($claim->amount,$to);
        $claim->update();
        if($to == 'approved') return (new Repayment())->setLoan($this->loanId)->setClaim($this->claimId)->setAmount($claim->amount)->storeCreditPaymentEntry();
    }

    /**
     * @param $amount
     * @param $to
     * @return string
     */
    public function getLog($amount, $to)
    {
        $log = [
            'approved' => '৳' . convertNumbersToBangla($amount) . ' লোন দাবি গৃহীত হয়েছে',
            'declined' => '৳' . convertNumbersToBangla($amount) . ' লোন দাবি বাতিল করা হয়েছে',
            'pending' => '৳' . convertNumbersToBangla($amount) . ' লোন দাবি করা হয়েছে'
        ];

        return $log[$to];
    }

    public function getClaimAmount()
    {
        $claim_request = LoanClaimModel::find($this->claimId);
        return $claim_request ? $claim_request->amount : 0;
    }

    public function getAffiliate()
    {
        $claim_request = LoanClaimModel::find($this->claimId);
        return $claim_request->resource->profile->affiliate;
    }

    /**
     * @param $data
     * @return bool
     */
    public function createRequest($data)
    {
        $this->loanClaimRequest = new LoanClaimModel($this->withCreateModificationField($data));
        return $this->loanClaimRequest->save();
    }

    /**
     * @param $loan_id
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getByYearAndMonth($loan_id, $year, $month)
    {
        return (new LoanClaimRepo(new LoanClaimModel()))->getByYearAndMonth($loan_id,$year,$month);
    }

    /**
     * @param $loan_id
     * @return mixed
     */
    public function getAll($loan_id)
    {
        return (new LoanClaimRepo(new LoanClaimModel()))->getByLoanID($loan_id);

    }

    /**
     * @param $loan_id
     * @return mixed
     */
    public function getRecent($loan_id)
    {
        return (new LoanClaimRepo(new LoanClaimModel()))->getRecent($loan_id);
    }

    /**
     * @param $loan_id
     * @return mixed
     */
    public function getPending($loan_id)
    {
        return (new LoanClaimRepo(new LoanClaimModel()))->getPending($loan_id);
    }
}