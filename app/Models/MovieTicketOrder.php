<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\PayableType;
use Sheba\Voucher\Contracts\CanHaveVoucher;

class MovieTicketOrder extends Model implements CanHaveVoucher, PayableType
{
    protected $guarded = ['id'];
    private $appliedDiscount;
    private $netBill;

    public function calculate()
    {
        $this->appliedDiscount = ($this->discount > $this->amount) ? $this->amount : $this->discount;
        $this->netBill = $this->amount - $this->appliedDiscount;

        return $this;
    }

    public function agent()
    {
        return $this->morphTo();
    }

    public function isFailed()
    {
        return $this->status == 'Failed';
    }

    public function isSuccess()
    {
        return $this->status == 'Success';
    }

    /**
     * @return mixed
     */
    public function getAppliedDiscount()
    {
        return $this->appliedDiscount;
    }

    /**
     * @return mixed
     */
    public function getNetBill()
    {
        return $this->netBill;
    }
}
