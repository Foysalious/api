<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $guarded = ['id'];

    public function members()
    {
        return $this->belongsToMany(Member::class);
    }

    public function deliveryAddresses()
    {
        return $this->hasMany(BusinessDeliveryAddress::class);
    }

    public function bankInformations()
    {
        return $this->hasMany(BusinessBankInformations::class);
    }

    public function requests()
    {
        return $this->hasMany(MemberRequest::class)->where('requester_type', 'business');
    }

    public function businessCategory()
    {
        return $this->belongsTo(BusinessCategory::class);
    }
}
