<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Location\Distance\TransactionMethod;
use Sheba\Payment\Wallet;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpTrait;

class Affiliate extends Model implements TopUpAgent
{
    use TopUpTrait;
    use Wallet;
    protected $guarded = ['id'];
    protected $dates = ['last_suspended_at'];
    protected $casts = ['wallet' => 'double', 'is_ambassador' => 'int', 'is_suspended' => 'int'];

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

    public function scopeAgentsWithoutFilter($query, $request)
    {
        $affiliate = $request->affiliate;
        list($sort, $order) = calculateSort($request);
        return $query->select('affiliates.profile_id', 'affiliates.id', 'affiliates.ambassador_id', 'affiliates.total_gifted_number', 'affiliates.total_gifted_amount', 'profiles.name', 'profiles.pro_pic as picture', 'profiles.mobile')
            ->leftJoin('profiles', 'profiles.id', '=', 'affiliates.profile_id')
            ->orderBy('affiliates.total_gifted_amount', $order)
            ->where('affiliates.ambassador_id', $affiliate->id);
    }

    public function scopeAgentsWithFilter($query, $request)
    {
        $affiliate = $request->affiliate;
        $order = calculateSort($request, 'affiliates.id')[1];
        return $query->select( 'affiliations.affiliate_id as id', 'aff2.profile_id', 'aff2.ambassador_id', 'profiles.name', 'profiles.pro_pic as picture', 'profiles.mobile')
            ->leftJoin('affiliate_transactions','affiliate_transactions.affiliate_id', '=', 'affiliates.id')
            ->leftJoin('affiliations', 'affiliate_transactions.affiliation_id', ' = ', 'affiliations.id')
            ->leftJoin('affiliates as aff2', 'affiliations.affiliate_id', '=', 'aff2.id')
            ->leftJoin('profiles', 'profiles.id', '=', 'aff2.profile_id')
            ->selectRaw('sum(affiliate_transactions.amount) as total_gifted_amount,count(distinct(affiliate_transactions.id)) as total_gifted_number')
            ->where('affiliates.id', $affiliate->id)
            ->where('affiliate_transactions.created_at','>','aff2.under_ambassador_since')
            ->where('affiliate_transactions.is_gifted',1)
            ->whereBetween('affiliate_transactions.created_at', getRangeFormat($request))
            ->orderBy('total_gifted_amount', $order)
            ->groupBy('affiliations.affiliate_id');
    }

    public function totalLead()
    {
        return $this->affiliations->where('status', 'successful')->count();
    }

    public function pendingLead()
    {
        return $this->affiliations->whereIn('status', ['pending','follow_up','converted'])->count();
    }

    public function successFullLead()
    {
        return $this->affiliations->where('status', 'successful')->count();
    }

    public function rejectedLead()
    {
        return $this->affiliations->where('status', 'rejected')->count();
    }

//    public function countStatusAffiliate($from, $to ,$statuses = []) {
//        if(sizeof($this->affiliations)>0){
//            if(sizeof($statuses)>0) {
//                return $this->affiliations->whereDate('created_at','>',$from)->whereDate('created_at','<',$to)->whereIn('status', $statuses)->count();
//            }
//            else
//                return $this->affiliations->whereDate('created_at','>',$from)->whereDate('created_at','<',$to)->count();
//        }
//    }
//
//    public function countStatusPartnerAffiliate($from, $to ,$status) {
//        return $this->partnerAffiliations->whereDate('created_at','>',$from)->whereDate('created_at','<',$to)->where('status', $status)->count();
//    }


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
        $this->debitWallet($amount);
        $this->walletTransaction(['amount' => $amount, 'type' => 'Debit', 'log' => $log]);
    }

    public function debitWallet($amount)
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

    public function bonuses()
    {
        return $this->morphMany(Bonus::class, 'user');
    }
}
