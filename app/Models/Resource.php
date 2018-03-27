<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $guarded = [
        'id'
    ];

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function associatePartners()
    {
        return $this->partners->unique();
    }

    public function firstPartner()
    {
        return $this->associatePartners()->first();
    }

    public function partnerResources()
    {
        return $this->hasMany(PartnerResource::class);
    }

    public function typeIn($partner)
    {
        $partner = $partner instanceof Partner ? $partner->id : $partner;
        $types = [];
        foreach ($this->partners()->withPivot('resource_type')->where('partner_id', $partner)->get() as $unique_partner) {
            $types[] = $unique_partner->pivot->resource_type;
        }
        return $types;
    }

    public function isOfTypesIn(Partner $partner, $types)
    {
        return boolval(count(array_intersect($types, $this->typeIn($partner))));
    }

    public function isManager(Partner $partner)
    {
        return $this->isOfTypesIn($partner, ["Admin", "Operation", "Owner", "Management", "Finance"]);
    }

    public function isAdmin(Partner $partner)
    {
        return $this->isOfTypesIn($partner, ["Admin", "Owner"]);
    }

    public function categoriesIn($partner)
    {
        $categories = collect();
        $partner_resources = ($this->partnerResources()->where('partner_id', $partner)->get())->load('categories');
        foreach ($partner_resources as $partner_resource) {
            foreach ($partner_resource->categories as $item) {
                $categories->push($item);
            }
        }
        return $categories->unique('id');
    }

    public function scopeVerified($query)
    {
        return $query->where('resources.is_verified', 1);
    }

    public function scopeType($query, $type)
    {
        return $query->where('resource_type', $type);
    }

    public function resourceSchedules()
    {
        return $this->hasMany(ResourceSchedule::class);
    }
}
