<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        return $this->type == 'advanced';
    }

}