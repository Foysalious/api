<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $guarded = ['id'];

    public function bidItems()
    {
        return $this->hasMany(BidItem::class);
    }

    public function procurements()
    {
        return $this->belongsToMany(Procurement::class);
    }
}