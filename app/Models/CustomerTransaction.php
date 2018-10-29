<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CustomerTransaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function partnerOrder()
    {
        return $this->belongsTo(PartnerOrder::class);
    }

}