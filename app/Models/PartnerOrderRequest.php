<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\OrderRequest\Status;

class PartnerOrderRequest extends Model
{
    protected $guarded = ['id'];

    public function partnerOrder()
    {
        return $this->belongsTo(PartnerOrder::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpenRequest($query)
    {
        return $query->where('status', '<>', Status::MISSED);
    }
}
