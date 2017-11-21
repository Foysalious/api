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

    public function typeIn($partner)
    {
        $partner = $partner instanceof Partner ? $partner->id : $partner;
        $types = [];
        foreach ($this->partners()->withPivot('resource_type')->where('partner_id', $partner)->get() as $unique_partner) {
            $types[] = $unique_partner->pivot->resource_type;
        }
        return $types;
    }

    public function isManager(Partner $partner)
    {
        return boolval(count(array_intersect(constants('MANAGER'), $this->typeIn($partner))));
    }

    public function scopeVerified($query)
    {
        return $query->where('resources.is_verified', 1);
    }

    public function scopeType($query, $type)
    {
        return $query->where('resource_type', $type);
    }
}
