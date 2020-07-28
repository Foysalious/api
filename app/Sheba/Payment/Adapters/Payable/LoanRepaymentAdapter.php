<?php


namespace Sheba\Payment\Adapters\Payable;


use App\Models\PartnerBankLoan;
use App\Models\Payable;

class LoanRepaymentAdapter implements PayableAdapter
{
    /** @var PartnerBankLoan */
    private $loan;
    private $amount;
    private $emiMonth;

    public function __construct() { $this->emiMonth = 0; }

    public function getPayable(): Payable
    {
        // TODO: Implement getPayable() method.
    }

    public function setModelForPayable($model)
    {
        // TODO: Implement setModelForPayable() method.
    }

    public function setEmiMonth($month)
    {
        $this->emiMonth = $month;
    }

    public function canInit(): bool
    {
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
}
