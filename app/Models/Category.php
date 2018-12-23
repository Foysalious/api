<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [
        'id'
    ];

    public function scopeParents($query)
    {
        $query->where([
            ['parent_id', null],
            ['publication_status', 1]
        ]);
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', 1);
    }

    public function scopeChild($query)
    {
        $query->where('parent_id', '<>', null);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->has('publishedServices', '>', 0)->published();
    }

    public function allChildren()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function publishedServices()
    {
        return $this->hasMany(Service::class)->published();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class)->withPivot(['commission', 'is_verified']);
    }

    public function partnerResources()
    {
        return $this->belongsToMany(PartnerResource::class);
    }

    public function isParent()
    {
        return $this->parent_id == null;
    }

    public function usps()
    {
        return $this->belongsToMany(Usp::class)->withPivot(['value']);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function commission($partner_id)
    {
        return (double)($this->partners()->wherePivot('partner_id', $partner_id)->first())->pivot->commission;
    }

    public function scopePublishedForBusiness($query)
    {
        return $query->where('is_published_for_business', 1);
    }

    public function scopePublishedForAll($query)
    {
        return $query->where('parent_id')->where(function ($query) {
            return $query->published()->orWhere('is_published_for_business', 1);
        });
    }

    public function scopePublishedOrPublishedForBusiness($query)
    {
        return $query->where(function ($query) {
            return $query->where('publication_status', 1)->orWhere('is_published_for_business', 1);
        });
    }

    public function isRentCar()
    {
        return in_array($this->id, array_map('intval', explode(',', env('RENT_CAR_IDS')))) ? 1 : 0;
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function sub()
    {
        return $this->hasMany(Category::class, 'parent_id')->published();
    }

    public function scopeLocationWise($query_, $hyper_locations)
    {
        return $query_->select('id', 'icon_png', 'name')->whereHas('locations', function ($q) use ($hyper_locations) {
            $q->whereIn('id', $hyper_locations);
        })->whereHas('sub', function ($qa) use ($hyper_locations) {
            $qa->whereHas('locations', function ($query) use ($hyper_locations) {
                $query->whereIn('id', $hyper_locations);
            });
        })->with(['children' => function ($qa) use ($hyper_locations) {
            $qa->whereHas('locations', function ($query) use ($hyper_locations) {
                $query->whereIn('id', $hyper_locations);
            });
        }]);
    }
}