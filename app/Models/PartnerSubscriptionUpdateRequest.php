<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerSubscriptionUpdateRequest extends Model
{
    protected $table = 'partner_package_update_requests';
    protected $guarded = ['id'];

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id);
    }

    public function oldPackage()
    {
        return $this->hasOne(PartnerSubscriptionPackage::class, 'id', 'old_package_id');
    }

    public function newPackage()
    {
        return $this->hasOne(PartnerSubscriptionPackage::class, 'id', 'new_package_id');
    }

    public function Partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
