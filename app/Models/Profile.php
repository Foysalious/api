<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'name',
        'pro_pic',
        'address',
        'email',
        'mobile',
        'password',
        'mobile_verified',
        'email_verified',
        'gender',
        "remember_token",
        "reference_code",
        "referrer_id",
        "created_by",
        "created_by_name",
        "updated_by",
        "updated_by_name"
    ];

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function resource()
    {
        return $this->hasOne(Resource::class);
    }

    public function affiliate()
    {
        return $this->hasOne(Affiliate::class);
    }

    public function member()
    {
        return $this->hasOne(Member::class);
    }

    public function joinRequests()
    {
        return $this->hasMany(JoinRequest::class);
    }

    public function getIdentityAttribute()
    {
        if ($this->name != '') {
            return $this->name;
        } elseif ($this->mobile) {
            return $this->mobile;
        }
        return $this->email;
    }
}
