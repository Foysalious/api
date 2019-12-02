<?php

namespace Sheba\Loan\DS;

use App\Models\PartnerBankLoan;
use Illuminate\Contracts\Support\Arrayable;
use Sheba\ModificationFields;

class PartnerLoanRequest implements Arrayable
{
    use ModificationFields;
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
        if ($this->partnerBankLoan)
            $this->setDetails();
    }

    public function setDetails()
    {
        $this->details = new LoanRequestDetails($this->partnerBankLoan);
    }

    /**
     * @return mixed
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param mixed $partner
     * @return PartnerLoanRequest
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
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

    public function create($data)
    {
        $data['partner_id']          = $this->partner->id;
        $data['status']              = constants('LOAN_STATUS')['considerable'];
        $data['interest_rate']       = constants('LOAN_CONFIG')['interest'];
        $data['monthly_installment'] = ((double)$data['amount'] + ((double)$data['amount'] * ($data['interest_rate'] / 100))) / ((int)$data['duration'] * 12);
        $this->setModifier($this->partner);
        $this->partnerBankLoan = new PartnerBankLoan($this->withCreateModificationField($data));
        $this->setDetails();
        $this->partnerBankLoan->save();
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
