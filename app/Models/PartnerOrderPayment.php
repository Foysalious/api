<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerOrderPayment extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];

    public static function boot()
    {
        parent::boot();

        self::created(function(PartnerOrderPayment $model){
            $model->partnerOrder->createOrUpdateReport();
        });

        self::updated(function(PartnerOrderPayment $model){
            $model->partnerOrder->createOrUpdateReport();
        });
    }

    public function partnerOrder()
    {
        return $this->belongsTo(PartnerOrder::class);
    }
}
