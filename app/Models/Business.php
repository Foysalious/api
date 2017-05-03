<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $guarded = ['id'];

    public function partnerOrder()
    {
        return $this->belongsTo(BusinessCategory::class);
    }

    public function associateMembers()
    {
        return $this->hasMany(BusinessMember::class);
    }

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
}
