<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\Wallet;

class Business extends Model
{
    use Wallet;
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


    public function bonuses()
    {
        return $this->morphMany(Bonus::class, 'user');
    }

    public function bonusLogs()
    {
        return $this->morphMany(BonusLog::class, 'user');
    }

    public function shebaCredit()
    {
        return $this->wallet + $this->shebaBonusCredit();
    }

    public function shebaBonusCredit()
    {
        return (double)$this->bonuses()->where('status', 'valid')->sum('amount');
    }

    public function transactions()
    {
        return $this->hasMany(CustomerTransaction::class);
    }

}
