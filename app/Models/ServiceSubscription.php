<?php namespace App\Models;

use Carbon\Carbon;
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
        $discount_offers = $this->discounts()->orderBy('subscription_type','desc')->get();
        $offers = collect();
        foreach($discount_offers as $offer)
        {
            if($offer->service_subscription_id === $this->id && $offer->isValid())
                $offers->push($offer);
        }
        if(count($offers)>0)
           return $this->parseDiscountOffers($offers[0]);
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

        $discount_amount_for_homepage = '';
        if($discount_offer->cap === 0.0) $discount_amount_for_homepage.="  ৳$discount_offer->cap";
        else $discount_amount_for_homepage .= $amount;
        if($this->service->unit)
        {
            $discount_amount_for_homepage.= '/';
            if($discount_offer->min_discount_qty) $discount_amount_for_homepage.="$discount_offer->min_discount_qty";
            $discount_amount_for_homepage.= "$unit";
        }

        return [
            'short_text' => $offer_short_text,
            'long_text' => $offer_long_text,
            'discount_amount_for_homepage' => $discount_amount_for_homepage
        ];
    }
}