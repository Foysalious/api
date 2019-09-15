<?php namespace Sheba\Pos\Discount\DTO\Params;

use App\Models\Voucher as VoucherModel;

class Voucher extends SetParams
{
    private $amount;
    /** @var Voucher $voucher */
    private $voucher;

    /**
     * @param VoucherModel $voucher
     * @return $this
     */
    public function setVoucher(VoucherModel $voucher)
    {
        $this->voucher = $voucher;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function getData()
    {
        return [
            'type' => $this->type,
            'amount' => $this->amount,
            'original_amount' => $this->voucher->amount,
            'is_percentage' => $this->voucher->is_amount_percentage,
            'cap' => $this->voucher->cap,
            'sheba_contribution' => $this->voucher->sheba_contribution,
            'partner_contribution' => $this->voucher->partner_contribution
        ];
    }
}