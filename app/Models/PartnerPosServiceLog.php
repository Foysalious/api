<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerPosServiceLog extends Model
{
    protected $guarded = ['id'];

    public function partnerPosService()
    {
        return $this->belongsTo(PartnerPosService::class);
    }

    public function getFieldNamesAttribute($value)
    {
        return collect(json_decode($value, true));
    }

    public function getOldValueAttribute($value)
    {
        return collect(json_decode($value, true));
    }

    public function getNewValueAttribute($value)
    {
        return collect(json_decode($value, true));
    }
}
