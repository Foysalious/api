<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubscription extends Model
{
    protected $guarded = ['id'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function discounts()
    {
        return $this->hasMany(ServiceSubscriptionDiscount::class);
    }

    public function scopeValidDiscounts()
    {
        return $this->with(['discounts' => function ($query) {
            return $query->valid();
        }]);
    }

    public function getParentCategoryAttribute()
    {
        return $this->service->category->parent->id;
    }

    public function getDiscountOffers() {
        $discount_offers = $this->validDiscounts()->first()->discounts()->orderBy('subscription_type','desc')->get();
        if($discount_offers)
           return $this->parseDiscountOffers($discount_offers[0]);
        else return null;
    }

    private function parseDiscountOffers($discount_offer)
    {
        $offer_short_text = "Subscribe & save ";
        $amount = $discount_offer->is_discount_amount_percentage ? $discount_offer->discount_amount . '%' : '৳' . $discount_offer->discount_amount;
        if($this->service->unit)
            $unit =$this->service->unit;

        if($discount_offer->cap === 0.0) $offer_short_text.=" upto ৳$discount_offer->cap";
        else $offer_short_text .= $amount;
        $offer_long_text = 'Save '.$amount;

        if($this->service->unit)
        {
            $offer_long_text.= ' in every ';
            if($discount_offer->min_discount_qty) $offer_long_text.="$discount_offer->min_discount_qty";
            $offer_long_text.= "$unit";
        }
        $offer_long_text.=' by subscribing!';
        return [
            'short_text' => $offer_short_text,
            'long_text' => $offer_long_text
        ];
    }
}