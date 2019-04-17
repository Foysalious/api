<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderItem extends Model
{
    protected $guarded = ['id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(PartnerPosService::class);
    }
}
