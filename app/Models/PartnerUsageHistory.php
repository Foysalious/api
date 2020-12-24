<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerUsageHistory extends Model
{
    protected $guarded = ['id'];
    protected $table   = 'partner_usages_history';

    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function scopeDays($query)
    {
        return $query->selectRaw('COUNT(*) as count')->groupBy('created_at')->limit(1);
    }
}
