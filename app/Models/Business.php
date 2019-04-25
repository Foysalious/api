<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $guarded = ['id'];

    public function members()
    {
        return $this->belongsToMany(Member::class)->withTimestamps()->withPivot('type', 'join_date');
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class, 'business_partners');
    }

    public function deliveryAddresses()
    {
        return $this->hasMany(BusinessDeliveryAddress::class);
    }

    public function bankInformations()
    {
        return $this->hasMany(BusinessBankInformations::class);
    }

    public function joinRequests()
    {
        return $this->morphMany(JoinRequest::class, 'organization');
    }

    public function businessCategory()
    {
        return $this->belongsTo(BusinessCategory::class);
    }

    public function shebaBonusCredit()
    {
        return 0;
    }
}
