<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'email',
        'mobile',
        'password',
        'mobile_verified',
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

    public function member()
    {
        return $this->hasOne(Member::class);
    }

}
