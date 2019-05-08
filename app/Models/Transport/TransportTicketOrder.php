<?php namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Sheba\Voucher\Contracts\CanHaveVoucher;

class TransportTicketOrder extends Model implements CanHaveVoucher
{
    protected $guarded = ['id'];

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
}
