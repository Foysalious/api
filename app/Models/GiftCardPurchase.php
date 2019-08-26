<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCardPurchase extends Model
{
    protected $guarded = ['id'];

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }
}
