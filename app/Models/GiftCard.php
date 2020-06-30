<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    public function scopeValid($query)
    {
        return $query->where([
            ['start_date', '<=', Carbon::now()],
            ['end_date', '>=', Carbon::now()]
        ]);
    }

    public function giftCardPurchases()
    {
        return $this->hasMany(GiftCardPurchase::class);
    }

    public function isValid()
    {
        return $this->start_date <= Carbon::now() && $this->end_date >= Carbon::now();
    }
}
