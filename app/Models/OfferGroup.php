<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferGroup extends Model
{
    protected $guarded = ['id'];

    public function offers()
    {
        return $this->belongsToMany(OfferShowcase::class, 'offer_group_offer');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_offer_group');
    }

    public function togglePublishedForApp()
    {
        $this->is_published_for_app = !$this->is_published_for_app;
        return $this;
    }

    public function togglePublishedForWeb()
    {
        $this->is_published_for_web = !$this->is_published_for_web;
        return $this;
    }
}
