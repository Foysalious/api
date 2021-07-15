<?php namespace App\Models;

use App\Sheba\InventoryService\Events\PartnerPosSettingUpdated;
use Illuminate\Database\Eloquent\Model;

class PartnerPosSetting extends Model
{

    protected $guarded = ['id'];
    protected $casts = ['vat_percentage' => 'double'];

    protected static function boot()
    {
        parent::boot();

        self::saved(function($partner_pos_setting) {
            event(new PartnerPosSettingUpdated($partner_pos_setting));
        });
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeByPartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id);
    }
}
