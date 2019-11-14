<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\BidStatusChangeLog\Model as BidStatusChangeLog;

class Bid extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['price' => 'double'];

    public function items()
    {
        return $this->hasMany(BidItem::class, 'bid_id');
    }

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function bidder()
    {
        return $this->morphTo();
    }

    public function isAdvanced()
    {
        return $this->procurement->type == 'advanced';
    }

    public function hasSentHireRequest()
    {
        return !in_array($this->status, ['pending', 'sent']);
    }

    public function statusChangeLogs()
    {
        return $this->hasMany(BidStatusChangeLog::class);
    }


    public function canSendHireRequest()
    {
        return !$this->procurement->hasAccepted() && !in_array($this->status, [config('b2b.BID_STATUSES')['sent'], config('b2b.BID_STATUSES')['rejected']]);
    }


}