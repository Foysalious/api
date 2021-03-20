<?php namespace App\Models;

use App\Models\Transport\TransportTicketOrder;
use App\Sheba\Payment\Rechargable;
use App\Sheba\Transactions\Wallet\RobiTopUpWalletTransactionHandler;
use Carbon\Carbon;
use Sheba\Dal\Affiliate\Events\AffiliateSaved;
use Sheba\Dal\BaseModel;
use Sheba\Dal\RobiTopupWalletTransaction\Model as RobiTopupWalletTransaction;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Sheba\MovieTicket\MovieAgent;
use Sheba\MovieTicket\MovieTicketTrait;
use Sheba\MovieTicket\MovieTicketTransaction;
use Sheba\Payment\PayableUser;
use Sheba\Reward\Rewardable;
use Sheba\Transactions\Types;
use Sheba\Wallet\Wallet;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpTrait;
use Sheba\TopUp\TopUpTransaction;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Transport\Bus\BusTicketCommission;
use Sheba\Transport\TransportAgent;
use Sheba\Transport\TransportTicketTransaction;
use Sheba\Voucher\Contracts\CanApplyVoucher;
use Sheba\Voucher\VoucherCodeGenerator;
use Sheba\Voucher\VoucherGeneratorTrait;

class Affiliate extends BaseModel implements Rewardable, TopUpAgent, MovieAgent, TransportAgent, CanApplyVoucher, Rechargable, HasWalletTransaction, PayableUser
{
    use TopUpTrait, MovieTicketTrait, Wallet, ModificationFields, VoucherGeneratorTrait;

    public static $savedEventClass = AffiliateSaved::class;
    protected $guarded = ['id'];
    protected $dates = ['last_suspended_at'];
    protected $casts = ['wallet' => 'double', 'is_ambassador' => 'int', 'is_suspended' => 'int', 'total_gifted_amount' => 'double'];
    protected $appends = ['joined'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function retailers()
    {
        /** @var Profile $profile */
        $profile = $this->profile;
        return $profile->retailers();
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

    public function onboardedPartners()
    {
        return $this->hasMany(Partner::class, 'affiliate_id');
    }

    public function moderatedPartners()
    {
        return $this->hasMany(Partner::class, 'moderator_id');
    }

    public function suspensions()
    {
        return $this->hasMany(AffiliateSuspension::class);
    }

    public function statusChangeLogs()
    {
        return $this->hasMany(AffiliateStatusChangeLog::class);
    }

    public function movieTicketOrders()
    {
        return $this->morphMany(MovieTicketOrder::class, 'agent');
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

    public function getJoinedAttribute()
    {
        return $this->under_ambassador_since ? Carbon::parse($this->under_ambassador_since)->diffForHumans() : null;
    }

    public function scopeAgentsWithoutFilter($query, $request)
    {
        $affiliate = $request->affiliate;
        list($sort, $order) = calculateSort($request);
        return $query->select('affiliates.profile_id', 'affiliates.id', 'affiliates.under_ambassador_since', 'affiliates.ambassador_id', 'affiliates.total_gifted_number', 'affiliates.total_gifted_amount', 'profiles.name', 'profiles.pro_pic as picture', 'profiles.mobile')->leftJoin('profiles', 'profiles.id', '=', 'affiliates.profile_id')->orderBy('affiliates.total_gifted_amount', $order)->where('affiliates.ambassador_id', $affiliate->id);
    }

    public function scopeAgentsWithFilter($query, $request, $tableName)
    {
        $affiliate = $request->affiliate;
        $rangeQuery = 'affiliate_transactions.is_gifted = 1 and affiliate_transactions.created_at > aff2.under_ambassador_since';
        if (isset($request->range) && !empty($request->range)) {
            $range = getRangeFormat($request);
            $rangeQuery = $rangeQuery . ' and `affiliate_transactions`.`created_at` BETWEEN \'' . $range[0]->toDateTimeString() . '\' AND \'' . $range[1]->toDateTimeString() . '\'';
        }
        return $query->select($tableName . '.affiliate_id as id', 'aff2.profile_id', 'aff2.ambassador_id', 'aff2.under_ambassador_since', 'profiles.name', 'profiles.pro_pic as picture', 'profiles.mobile', 'aff2.created_at')
            ->leftJoin('affiliate_transactions', 'affiliate_transactions.affiliate_id', '=', 'affiliates.id')
            ->leftJoin($tableName, 'affiliate_transactions.affiliation_id', ' = ', $tableName . '.id')
            ->leftJoin('affiliates as aff2', $tableName . '.affiliate_id', '=', 'aff2.id')
            ->leftJoin('profiles', 'profiles.id', '=', 'aff2.profile_id')
            ->selectRaw('sum(affiliate_transactions.amount) as total_gifted_amount, count(distinct(affiliate_transactions.id)) as total_gifted_number')
            ->where('affiliates.id', $affiliate->id)
            ->whereRaw($rangeQuery)
            ->groupBy($tableName . '.affiliate_id');
    }

    /*public function scopeAgentsWithFilter($query, $request, $tableName)
    {
        $affiliate = $request->affiliate;
        $rangeQuery = 'affiliate_transactions.is_gifted = 1     ';
        if (isset($request->range) && !empty($request->range)) {
            $range = getRangeFormat($request);
            $rangeQuery = $rangeQuery . ' and `affiliate_transactions`.`created_at` BETWEEN \'' . $range[0]->toDateTimeString() . '\' AND \'' . $range[1]->toDateTimeString() . '\'';
        }
        return $query->select('affiliate_transactions' . '.affiliate_id as id', 'affiliates.profile_id', 'affiliates.ambassador_id', 'affiliates.under_ambassador_since', 'profiles.name', 'profiles.pro_pic as picture', 'profiles.mobile', 'affiliates.created_at')
            ->leftJoin('affiliate_transactions', 'affiliate_transactions.affiliate_id', '=', 'affiliates.id')
            //->leftJoin($tableName, 'affiliate_transactions.affiliation_id', ' = ', $tableName . '.id')
            ->leftJoin('affiliates', 'affiliate_transactions' . '.affiliate_id', '=', 'affiliates.id')
            ->leftJoin('profiles', 'profiles.id', '=', 'affiliates.profile_id')
            ->selectRaw('sum(affiliate_transactions.amount) as total_gifted_amount, count(distinct(affiliate_transactions.id)) as total_gifted_number')
            ->where('affiliates.id', $affiliate->id)
            ->whereRaw($rangeQuery)
            ->groupBy('affiliate_transactions' . '.affiliate_id');
    }*/

    public function totalLead()
    {
        return $this->affiliations->where('status', 'successful')->count();
    }

    public function earningAmount()
    {
        $earning = $this->transactions()->earning()->sum('amount');
        return $earning ? (double)$earning : 0;
    }

    public function transactions()
    {
        return $this->hasMany(AffiliateTransaction::class);
    }

    public function robi_topup_wallet_transactions()
    {
        return $this->hasMany(RobiTopupWalletTransaction::class);
    }

    public function earningAmountDateBetween(TimeFrame $time_frame)
    {
        $earning = $this->transactions()->earning()
            ->whereBetween('created_at', $time_frame->getArray())
            ->sum('amount');

        return $earning ? (double)$earning : 0;
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

    public function topUpTransaction(TopUpTransaction $transaction)
    {
        if (!$transaction->getIsRobiTopUp()) {
            (new WalletTransactionHandler())
                ->setModel($this)
                ->setAmount($transaction->getAmount())
                ->setSource(TransactionSources::TOP_UP)
                ->setType(Types::debit())
                ->setLog($transaction->getLog())
                ->dispatch();
        } else {
            (new RobiTopupWalletTransactionHandler())->setModel($this)->setAmount($transaction->getAmount())->setLog($transaction->getLog())->setType(Types::debit())->store();
        }
    }

    public function walletTransaction($data)
    {
        $this->transactions()->save(new AffiliateTransaction($this->withCreateModificationField($data)));
    }

    public function isAmbassador()
    {
        return $this->is_ambassador == 1;
    }

    public function isVerified()
    {
        return $this->verification_status == 'verified';
    }

    public function isNotVerified()
    {
        return !$this->isVerified();
    }

    public function topups()
    {
        return $this->hasMany(TopUpOrder::class, 'agent_id')->where('agent_type', 'App\\Models\\Affiliate');
    }

    public function scopeTopUpTransactionBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeTopUpOperator($query, $operator)
    {
        return $query->where('vendor_id', $operator);
    }

    public function getCommission()
    {
        return new \Sheba\TopUp\Commission\Affiliate();
    }

    public function getMovieTicketCommission()
    {
        return new \Sheba\MovieTicket\Commission\Affiliate();
    }

    public function movieTicketTransaction(MovieTicketTransaction $transaction)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->debitWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Debit', 'log' => $transaction->getLog()]);*/
        (new WalletTransactionHandler())->setModel($this)->setAmount($transaction->getAmount())->setSource(TransactionSources::MOVIE)->setType(Types::debit())->setLog($transaction->getLog())->dispatch();
    }

    public function movieTicketTransactionNew(MovieTicketTransaction $transaction)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->creditWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Credit', 'log' => $transaction->getLog()]);*/
        (new WalletTransactionHandler())->setModel($this)->setAmount($transaction->getAmount())->setSource(TransactionSources::MOVIE)->setType(Types::credit())->setLog($transaction->getLog())->dispatch();
    }

    public function transportTicketOrders()
    {
        return $this->morphMany(TransportTicketOrder::class, 'agent');
    }

    /**
     * @return BusTicketCommission|\Sheba\Transport\Bus\Commission\Affiliate
     */
    public function getBusTicketCommission()
    {
        return new \Sheba\Transport\Bus\Commission\Affiliate();
    }

    public function transportTicketTransaction(TransportTicketTransaction $transaction)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->creditWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Credit', 'log' => $transaction->getLog()]);*/
        (new WalletTransactionHandler())->setModel($this)->setAmount($transaction->getAmount())->setSource(TransactionSources::TRANSPORT)->setType(Types::credit())->setLog($transaction->getLog())->dispatch();
    }

    public function shebaCredit()
    {
        return $this->wallet + $this->shebaBonusCredit();
    }

    public function shebaBonusCredit()
    {
        return (double)$this->bonuses()->where('status', 'valid')->sum('amount');
    }

    public function bonuses()
    {
        return $this->morphMany(Bonus::class, 'user');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function getTagListAttribute()
    {
        return $this->tags->pluck('id')->toArray();
    }

    public function getTagNamesAttribute()
    {
        return $this->tags->pluck('name');
    }

    public function getIncome()
    {
        return $this->transactions()->where('type', 'Credit')->where('log', '<>', 'Credit Purchase')->sum('amount');
    }

    public function getMobile()
    {
        return $this->profile->mobile;
    }

    public function generateReferral()
    {
        if ($this->profile->mobile)
            return formatMobileReverse($this->profile->mobile);

        return VoucherCodeGenerator::byName($this->profile->name);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Affiliation::class);

    }
}
