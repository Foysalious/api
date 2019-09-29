<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $guarded = ['id'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class)->withTimestamps();
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function businessMember()
    {
        return $this->hasOne(BusinessMember::class);
    }

    public function typeIn($business)
    {
        $business = $business instanceof Business ? $business->id : $business;
        $types = [];
        foreach ($this->businesses()->withPivot('type')->where('business_id', $business)->get() as $unique_business) {
            $types[] = $unique_business->pivot->type;
        }
        return $types;
    }

    public function isOfTypesIn(Business $business, $types)
    {
        return boolval(count(array_intersect($types, $this->typeIn($business))));
    }

    public function isManager(Business $business)
    {
        return $this->isOfTypesIn($business, ["Admin"]);
    }

    public function customer()
    {
        return $this->profile->customer();
    }
}
