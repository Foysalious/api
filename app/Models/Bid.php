<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $guarded = ['id'];

    public function bidItems()
    {
        return $this->hasMany(BidItem::class, 'bid_id');
    }

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }

}