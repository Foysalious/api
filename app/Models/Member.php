<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Business\CoWorker\Statuses;
use Sheba\Dal\Expense\Expense;

class Member extends Model
{
    protected $guarded = ['id'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function allBusinesses()
    {
        return $this->belongsToMany(Business::class)->withTimestamps();
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class)->whereIn('status', Statuses::getAccessible())->withTimestamps();
    }

    public function inactiveBusinesses()
    {
        return $this->belongsToMany(Business::class)->where('status', Statuses::INACTIVE)->withTimestamps();
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function businessMember()
    {
        return $this->businessMembers()->whereIn('status', Statuses::getAccessible());
    }

    public function inactiveBusinessMember()
    {
        return $this->businessMembers()->where('status', Statuses::INACTIVE);
    }

    public function businessMemberWithoutStatusCheck()
    {
        return $this->businessMembers()->first();
    }

    public function getBusinessMemberAttribute()
    {
        return $this->businessMembers()->whereIn('status', Statuses::getAccessible())->first();
    }

    public function businessMembers()
    {
        return $this->hasMany(BusinessMember::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
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

    public function getIdentityAttribute()
    {
        if ($this->profile->name != '') {
            return $this->profile->name;
        } elseif ($this->profile->mobile) {
            return $this->profile->mobile;
        }
        return $this->profile->email;
    }

    public function scopeActive($query)
    {
        return $query->where('status', Statuses::ACTIVE);
    }
}
