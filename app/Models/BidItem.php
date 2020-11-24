<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BidItem extends Model
{
    protected $guarded = ['id'];

    public function fields()
    {
        return $this->hasMany(BidItemField::class, 'bid_item_id');
    }
}