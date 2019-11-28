<?php


namespace Sheba\Loan\DS;


use App\Models\PartnerBankLoan;
use Illuminate\Contracts\Support\Arrayable;

class PartnerLoanRequest implements Arrayable
{
    public $partnerBankLoan;
    public $partner;
    public $bank;
    public $loan_amount;
    public $status;
    public $duration;
    public $monthly_installment;
    public $interest_rate;
    public $details;
    public $created_by;
    public $updated_by;
    public $created;
    public $updated;

    public function __construct(PartnerBankLoan $partnerBankLoan = null)
    {
        $this->partnerBankLoan = $partnerBankLoan;
        if ($this->partnerBankLoan) $this->setDetails();
    }

    public function setDetails()
    {
        $this->details=new LoanRequestDetails($this->partnerBankLoan->final_information_for_loan);
    }

    /**
     * @param PartnerBankLoan $partnerBankLoan
     * @return PartnerLoanRequest
     */
    public function setPartnerBankLoan($partnerBankLoan)
    {
        $this->partnerBankLoan = $partnerBankLoan;
        return $this;
    }


    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'            => $this->partnerBankLoan->id,
            'partner'       => $this->partner,
            'bank'          => $this->bank,
            'bank_name'     => $this->bank ? $this->bank->name : null,
            'logo'          => $this->bank ? $this->bank->logo : null,
            'duration'      => $this->duration,
            'interest_rate' => $this->interest_rate,
            'status'        => $this->status,
            'details'       => $this->details
        ];
    }
}
