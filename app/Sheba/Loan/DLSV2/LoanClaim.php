<?php namespace App\Sheba\Loan\DLSV2;


use Sheba\Dal\LoanClaimRequest\Model;
use Sheba\ModificationFields;
use Sheba\Dal\LoanClaimRequest;

class LoanClaim
{

    use ModificationFields;
    private $loanClaimRequest;
    private $loanId;
    private $claimId;

    public function setLoan($loan_id)
    {
         $this->loanId = $loan_id;
         return $this;
    }

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
        return Model::where('loan_id',$this->loanId)->orderBy('id','desc')->first();

        //return Model::lastClaim($this->loanId);
    }

    public function updateStatus($to)
    {

        $claim = Model::where('id',$this->claimId)->first();
        $claim->status = $to;
        //***
        $claim->log = $to == 'approved' ? '৳' .convertNumbersToBangla($claim->amount) .' লোন দাবি গৃহীত হয়েছে' : '৳' .convertNumbersToBangla($claim->amount) .' লোন দাবি বাতিল করা হয়েছে';
        return $claim->update();
    }

    public function createRequest($data)
    {
        $this->loanClaimRequest = new LoanClaimRequest\Model($this->withCreateModificationField($data));
        $this->loanClaimRequest->save();
        return $this->loanClaimRequest;
    }

    public function getByYearAndMonth($loan_id,$year,$month)
    {
        return Model::where('loan_id',$loan_id)
            ->whereYear('created_at','=',$year)->whereMonth('created_at','=',$month)
            ->get();

    }

    public function getAll($loan_id)
    {
        return Model::where('loan_id',$loan_id)->get();

    }



    public function getRecent($loan_id)
    {
        return Model::where('loan_id',$loan_id)->orderBy('id','desc')->take(3)->get();

    }

    public function getPending($loan_id)
    {
        return Model::where('loan_id',$loan_id)->where('status','pending')->orderBy('id','desc')->first();
    }
}