<?php namespace App\Sheba\Loan\DLSV2;


use Sheba\Dal\LoanClaimRequest\Model;
use Sheba\ModificationFields;
use Sheba\Dal\LoanClaimRequest;

class LoanClaim
{

    use ModificationFields;
    private $loanClaimRequest;
    private $loanId;

    public function setLoan($loan_id)
    {
         $this->loanId = $loan_id;
         return $this;
    }

    /**
     * @return mixed
     */
    public function lastClaim()
    {
        return Model::where('loan_id',$this->loanId)->orderBy('id','desc')->first();

        //return Model::lastClaim($this->loanId);
    }

    public function createRequest($data)
    {
        $this->loanClaimRequest = new LoanClaimRequest\Model($this->withCreateModificationField($data));
        $this->loanClaimRequest->save();
        return $this->loanClaimRequest;
    }

    public function getAll($loan_id)
    {
        return Model::where('loan_id',$loan_id)->get();

    }
}