<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerServicePricesUpdate extends Model
{
    protected $guarded = ['id'];

    protected $table = 'partner_service_prices_update';

    public function partnerService()
    {
        return $this->belongsTo(PartnerService::class);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
