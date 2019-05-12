<?php namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Sheba\Voucher\Contracts\CanHaveVoucher;

class TransportTicketOrder extends Model implements CanHaveVoucher
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
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

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function vendor()
    {
        return $this->belongsTo(TransportTicketVendor::class);
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
