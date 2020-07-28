<?php


namespace Sheba\Payment\Adapters\Payable;


use App\Models\Partner;
use App\Models\PartnerBankLoan;
use App\Models\Payable;
use Carbon\Carbon;

class LoanRepaymentAdapter implements PayableAdapter
{
    const TYPE='partner_bank_loan';
    /** @var PartnerBankLoan */
    private $loan;
    private $amount;
    private $emiMonth;
    private $user;

    public function __construct() { $this->emiMonth = 0; }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = self::TYPE ;
        $payable->type_id=$this->loan->id;
        $payable->user_id = $this->user->id;
        $payable->user_type = get_class($this->user);
        $payable->amount = (double)$this->amount;
        $payable->completion_type = self::TYPE;
        $payable->success_url = $this->getSuccessUrl();
        $payable->fail_url =$this->getFailedUrl();
        $payable->created_at = Carbon::now();
        $payable->save();
        return $payable;
    }

    public function setModelForPayable($model)
    {

    }

    public function setEmiMonth($month)
    {
        $this->emiMonth = $month;
    }

    public function canInit(): bool
    {
        if(empty($this->loan)){
            return false;
        }
        return true;
    }

    /**
     * @param mixed $loan
     * @return LoanRepaymentAdapter
     */
    public function setLoan(PartnerBankLoan $loan)
    {
        $this->loan = $loan;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return LoanRepaymentAdapter
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
    public function getSuccessUrl(){
        return ($this->user instanceof Partner) ? config('sheba.partners_url') . '/loan-repayment-success' : config('sheba.front_url') . '/profile/credit';
    }
    public function getFailedUrl(){
        return ($this->user instanceof Partner) ? config('sheba.partners_url') . '/loan-repayment-failed' : null;
    }

    /**
     * @param mixed $user
     * @return LoanRepaymentAdapter
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
}
