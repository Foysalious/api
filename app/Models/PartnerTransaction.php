<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
