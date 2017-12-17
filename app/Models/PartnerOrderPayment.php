<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerOrderPayment extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];

    public function partnerOrder()
    {
        return $this->belongsTo(PartnerOrder::class);
    }
}
