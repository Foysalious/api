<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $guarded = ['id'];


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
