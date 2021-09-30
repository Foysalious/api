<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Transactions\Types;

class PartnerTransaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
    public $timestamps = false;

    public function partner_order()
    {
        return $this->belongsTo(PartnerOrder::class);
    }

    public function partnerOrder()
    {
        return $this->belongsTo(PartnerOrder::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function scopeHasTransactionID($query, $transactionId)
    {
        $query->where('transaction_details', 'LIKE', '%"id":"' . $transactionId . '"%')->orWhere('transaction_details','LIKE','%"trxID":"' . $transactionId . '"%');
    }

    public function scopePaymentLinkCredit($query)
    {
        $query->where('log', 'LIKE', '%has been collected from%')->where('type', Types::CREDIT);
    }
}
