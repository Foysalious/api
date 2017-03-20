<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{

    protected $fillable = [
        'mobile', 'remember_token', 'password', 'email', 'mobile_verified', 'reference_code', 'referrer_id', 'profile_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function mobiles()
    {
        return $this->hasMany(CustomerMobile::class);
    }

    public function delivery_addresses()
    {
        return $this->hasMany(CustomerDeliveryAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function custom_orders()
    {
        return $this->hasMany(CustomOrder::class);
    }
}
