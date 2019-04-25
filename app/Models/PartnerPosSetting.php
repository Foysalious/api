<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerPosSetting extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['vat_percentage' => 'double'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeByPartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id);
    }
}
