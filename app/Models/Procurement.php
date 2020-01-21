<?php namespace App\Models;

use Sheba\Dal\ProcurementPaymentRequest\Model as ProcurementPaymentRequest;
use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\PayableType;

class Procurement extends Model implements PayableType
{
    protected $guarded = ['id'];
    public $paid;
    public $due;
    public $totalPrice;
    protected $dates = ['closed_and_paid_at'];

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
        return $this->bids->where('status', config('b2b.BID_STATUSES')['accepted'])->first();
    }

    public function calculate()
    {
        if ($this->paid) return;
        $bid = $this->getActiveBid();
        $this->paid = $this->sheba_collection + $this->partner_collection;
        $this->due = $bid ? $bid->price - $this->paid : 0;
        $this->totalPrice = $bid ? $bid->price : null;
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function hasAccepted()
    {
        return $this->status == config('b2b.PROCUREMENT_STATUS')['accepted'];
    }

    public function isServed()
    {
        return $this->status == config('b2b.PROCUREMENT_STATUS')['served'];
    }

    public function isClosedAndPaid()
    {
        return $this->closed_and_paid_at != null;
    }
}
