<?php namespace App\Sheba\Loan\DLSV2;


use Sheba\ModificationFields;
use Sheba\Dal\LoanClaimRequest;

class LoanClaim
{

    use ModificationFields;
    private $loanClaimRequest;
    private $loanId;

    public function setLoan($loan_id)
    {
        return $this->loanID = $loan_id;
    }

    public function lastClaim()
    {
        return LoanClaimRequest\Model::lastClaim($this->loanId);
    }

    public function createRequest($data)
    {
        $this->loanClaimRequest = new LoanClaimRequest\Model($this->withCreateModificationField($data));
        $this->loanClaimRequest->save();
        return $this->loanClaimRequest;
    }
}