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
}
