<?php namespace App\Models;

use App\Sheba\PosOrderService\PosSetting\Events\Created;
use App\Sheba\PosOrderService\PosSetting\Events\Updated;
use Sheba\Dal\BaseModel;


class PartnerPosSetting extends BaseModel
{

    protected $guarded = ['id'];
    protected $casts = ['vat_percentage' => 'double'];

    public static $updatedEventClass = Updated::class;
    public static $createdEventClass = Created::class;


    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeByPartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id);
    }
}
