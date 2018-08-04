<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Location\Distance\DistanceStrategy;
use Sheba\TopUp\Mock;
use Sheba\TopUp\Robi;
use Sheba\TopUp\TopUp;
use Sheba\TopUp\OperatorAgent;
use Sheba\TopUp\TopUpVendor;
use Sheba\TopUpTrait;

class Affiliate extends Model implements OperatorAgent
{
    use TopUpTrait;
    protected $guarded = ['id'];
    protected $dates = ['last_suspended_at'];
    protected $casts = ['wallet' => 'double', 'is_ambassador' => 'int'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function affiliations()
    {
        return $this->hasMany(Affiliation::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function partnerAffiliations()
    {
        return $this->hasMany(PartnerAffiliation::class);
    }

    public function suspensions()
    {
        return $this->hasMany(AffiliateSuspension::class);
    }

    public function transactions()
    {
        return $this->hasMany(AffiliateTransaction::class);
    }

    public function getBankingInfoAttribute($info)
    {
        return $info ? json_decode($info) : [];
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function scopeSuspended($query)
    {
        return $query->where('is_suspended', 1);
    }

    public function scopeSuspensionOver($query)
    {
        constants('AFFILIATE_SUSPENSION_DAYS');
        $query->suspended()->where('last_suspended_at', '<', Carbon::now()->subHour()->toDateTimeString());
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function totalLead()
    {
        return $this->affiliations->where('status', 'successful')->count();
    }

    public function earningAmount()
    {
        return $this->transactions->where('type', 'Credit')->sum('amount');
    }

    public function ambassador()
    {
        return $this->belongsTo(Affiliate::class, 'ambassador_id');
    }

    public function agents()
    {
        return $this->hasMany(Affiliate::class, 'ambassador_id');
    }

    public function vouchers()
    {
        return $this->morphMany(Voucher::class, 'owner');
    }

    public function getReferralAttribute()
    {
        $vouchers = $this->vouchers;
        return $vouchers ? $vouchers->first() : null;
    }

    public function topUpTransaction($amount, $log)
    {
        $this->debitWallet($amount, $log);
        $this->walletTransaction(['amount' => $amount, 'type' => 'Debit', 'log' => $log]);
    }

    public function debitWallet($amount, $log)
    {
        $this->update(['wallet' => $this->wallet - $amount]);
    }

    public function walletTransaction($data)
    {
        $this->transactions()->save(new AffiliateTransaction(array_merge($data, createAuthor($this))));
    }

    public function isAmbassador()
    {
        return $this->is_ambassador == 1;
    }
}
