<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerOrderStatusLog extends Model
{
    public $timestamps = false;
    protected $dates = ['created_at'];
    protected $guarded = ['id'];

    public function partnerOrder()
    {
        return $this->belongsTo(PartnerOrder::class);
    }
}
