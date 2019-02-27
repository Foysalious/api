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
        $discount_offers = $this->validDiscounts()->first()->discounts;
        if($discount_offers)
            $this->parseDiscountOffers($discount_offers);
        else return null;
    }

    private function parseDiscountOffers($discount_offers)
    {
        $offer_short_text = "Subscribe & save upto ";
        $amount = $subscription->is_discount_amount_percentage ? $subscription->discount_amount . '%' : '৳' . $subscription->discount_amount;
        if($subscription->service->unit)
            $unit =$subscription->service->unit;

        $offer_short_text .= $amount;
        $offer_long_text = 'Save '.$amount;

        if($subscription->service->unit)
        {
            $offer_short_text.='/'.$unit;
            $offer_long_text.= ' in every '.$unit;
        }
        $offer_long_text.=' by subscribing!';
        return [
            'short_text' => $offer_short_text,
            'long_text' => $offer_long_text
        ];
    }
}