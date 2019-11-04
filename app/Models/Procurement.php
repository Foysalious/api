<?php namespace App\Models;


use Sheba\Dal\ProcurementPaymentRequest\Model as ProcurementPaymentRequest;
use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\PayableType;

class Procurement extends Model implements PayableType
{
    protected $guarded = ['id'];
    public $paid;
    public $due;

    public function items()
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function questions()
    {
        return $this->hasMany(ProcurementQuestion::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function paymentRequests()
    {
        return $this->hasMany(ProcurementPaymentRequest::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function getTagNamesAttribute()
    {
        return $this->tags->pluck('name');
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function scopeOrder($query)
    {
        return $query->whereIn('status', ['accepted', 'started', 'served', 'cancelled']);
    }

    public function getActiveBid()
    {
        return $this->bids->where('status', config('b2b.BID_STATUSES')['awarded'])->first();
    }

    public function calculate()
    {
        $bid = $this->getActiveBid();
        $this->paid = $this->sheba_collection + $this->partner_collection;
        $this->due = $this->paid - $bid ? $bid->price : 0;
    }

}