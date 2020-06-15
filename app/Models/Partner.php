<?php namespace App\Models;

use App\Models\Transport\TransportTicketOrder;
use App\Sheba\Payment\Rechargable;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Sheba\Business\Bid\Bidder;
use Sheba\Dal\BaseModel;
use Sheba\Dal\Complain\Model as Complain;
use Sheba\Dal\PartnerOrderPayment\PartnerOrderPayment;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Wallet\HasWallet;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use Sheba\MovieTicket\MovieAgent;
use Sheba\MovieTicket\MovieTicketTrait;
use Sheba\MovieTicket\MovieTicketTransaction;
use Sheba\Partner\BadgeResolver;
use Sheba\Partner\PartnerStatuses;
use Sheba\Wallet\Wallet;
use Sheba\Referral\HasReferrals;
use Sheba\Resource\ResourceTypes;
use Sheba\Reward\Rewardable;
use Sheba\Subscription\Partner\PartnerSubscriber;
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

class Partner extends BaseModel implements Rewardable, TopUpAgent, HasWallet, TransportAgent, CanApplyVoucher, MovieAgent, Rechargable, Bidder, HasWalletTransaction, HasReferrals
{
    use Wallet, TopUpTrait, MovieTicketTrait;

    public    $totalCreditForSubscription;
    public    $totalPriceRequiredForSubscription;
    public    $creditBreakdown;
    protected $guarded              = ['id'];
    protected $dates                = [
        'last_billed_date',
        'billing_start_date'
    ];
    protected $casts                = [
        'wallet'              => 'double',
        'last_billed_amount'  => 'double',
        'reward_point'        => 'int',
        'current_impression'  => 'double',
        'impression_limit'    => 'double',
        'uses_sheba_logistic' => 'int'
    ];
    protected $resourcePivotColumns = [
        'id',
        'designation',
        'department',
        'resource_type',
        'is_verified',
        'verification_note',
        'created_by',
        'created_by_name',
        'created_at',
        'updated_by',
        'updated_by_name',
        'updated_at'
    ];
    protected $categoryPivotColumns = [
        'id',
        'experience',
        'preparation_time_minutes',
        'response_time_min',
        'response_time_max',
        'commission',
        'is_verified',
        'uses_sheba_logistic',
        'verification_note',
        'created_by',
        'created_by_name',
        'created_at',
        'updated_by',
        'updated_by_name',
        'updated_at',
        'is_home_delivery_applied',
        'is_partner_premise_applied',
        'delivery_charge'
    ];
    protected $servicePivotColumns  = [
        'id',
        'description',
        'options',
        'prices',
        'min_prices',
        'base_prices',
        'base_quantity',
        'is_published',
        'discount',
        'discount_start_date',
        'discount_start_date',
        'is_verified',
        'verification_note',
        'created_by',
        'created_by_name',
        'created_at',
        'updated_by',
        'updated_by_name',
        'updated_at'
    ];
    private   $resourceTypes;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->resourceTypes = constants('RESOURCE_TYPES');
    }

    public function basicInformations()
    {
        return $this->hasOne(PartnerBasicInformation::class);
    }

    public function financeResources()
    {
        return $this->belongsToMany(Resource::class)->where('resource_type', constants('RESOURCE_TYPES')['Finance'])->withPivot($this->resourcePivotColumns);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)->withPivot($this->servicePivotColumns);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_partners');
    }

    public function categoryRequests()
    {
        return $this->hasMany(CategoryRequest::class);
    }

    public function getLocationsList()
    {
        return $this->locations->lists('id')->toArray();
    }

    public function getLocationsNames()
    {
        return $this->locations->lists('name')->toArray();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orders()
    {
        return $this->hasMany(PartnerOrder::class);
    }

    public function jobs()
    {
        return $this->hasManyThrough(Job::class, PartnerOrder::class);
    }

    public function complains()
    {
        return $this->hasMany(Complain::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(PartnerOrderPayment::class, PartnerOrder::class);
    }

    public function partner_orders()
    {
        return $this->hasMany(PartnerOrder::class);
    }

    public function partnerOrders()
    {
        return $this->hasMany(PartnerOrder::class);
    }

    public function todayOrders()
    {
        return $this->hasMany(PartnerOrder::class)->whereDate('created_at', '=', Carbon::today());
    }

    public function walletSetting()
    {
        return $this->hasOne(PartnerWalletSetting::class);
    }

    public function workingHours()
    {
        return $this->hasMany(PartnerWorkingHour::class);
    }

    public function dailyStats()
    {
        return $this->hasMany(PartnerDailyStat::class);
    }

    public function topups()
    {
        return $this->hasMany(TopUpOrder::class, 'agent_id')->where('agent_type', 'App\\Models\\Partner');
    }

    public function commission($service_id)
    {
        $service_category = Service::find($service_id)->category->id;
        return $this->categories()->find($service_category)->pivot->commission;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withPivot($this->categoryPivotColumns);
    }

    public function loan()
    {
        return $this->hasMany(PartnerBankLoan::class);
    }

    public function onIndefiniteLeave()
    {
        $leave = $this->runningLeave();
        return ($leave && !$leave->end_date) ? true : false;
    }

    public function runningLeave($date = null)
    {
        $date = ($date) ? (($date instanceof Carbon) ? $date : new Carbon($date)) : Carbon::now();
        foreach ($this->leaves()->whereDate('start', '<=', $date)->get() as $leave) {
            if ($leave->isRunning($date))
                return $leave;
        }
        return null;
    }

    public function leaves()
    {
        return $this->hasMany(PartnerLeave::class);
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

    public function hasLeave($date)
    {
        $date = $date == null ? Carbon::now() : new Carbon($date);
        foreach ($this->leaves as $leave) {
            if ($date->between($leave->start, $leave->end)) {
                return true;
            }
        }
        return false;
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

    public function getContactResource()
    {
        if ($operation_resource = $this->getFirstOperationResource())
            return $operation_resource;
        if ($admin_resource = $this->getFirstAdminResource())
            return $admin_resource;
        return null;
    }

    public function getFirstOperationResource()
    {
        if ($this->resources) {
            return $this->resources->where('pivot.resource_type', ResourceTypes::OPERATION)->first();
        } else {
            return $this->admins->first();
        }
    }

    public function getFirstAdminResource()
    {
        if ($this->resources) {
            return $this->resources->where('pivot.resource_type', ResourceTypes::ADMIN)->first();
        } else {
            return $this->admins->first();
        }
    }

    public function generateReferral()
    {
        return VoucherCodeGenerator::byName($this->name);
    }

    public function scopePublished($query)
    {
        return $query->where('status', PartnerStatuses::VERIFIED);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', PartnerStatuses::VERIFIED);
    }

    public function isVerified()
    {
        return $this->status === PartnerStatuses::VERIFIED;
    }

    public function getContactNumber()
    {
        $resource = $this->getContactResource();
        if(!$resource) return null;
        return $resource->profile->mobile;
    }

    public function getContactResourceProPic()
    {
        $resource = $this->getContactResource();
        if(!$resource) return null;
        return $resource->profile->pro_pic;
    }

    public function isNIDVerified()
    {
        if ($operation_resource = $this->operationResources()->first())
            return $operation_resource->profile->nid_verified;
        if ($admin_resource = $this->admins()->first())
            return $admin_resource->profile->nid_verified;
        return null;
    }

    public function operationResources()
    {
        return $this->belongsToMany(Resource::class)->where('resource_type', constants('RESOURCE_TYPES')['Operation'])->withPivot($this->resourcePivotColumns);
    }

    public function admins()
    {
        return $this->belongsToMany(Resource::class)->where('resource_type', constants('RESOURCE_TYPES')['Admin'])->withPivot($this->resourcePivotColumns);
    }


    public function updatedAt()
    {
        if ($operation_resource = $this->operationResources()->first())
            return $operation_resource->profile->updated_at;
        if ($admin_resource = $this->admins()->first())
            return $admin_resource->profile->updated_at;
        return null;
    }

    public function getContactPerson()
    {
        if ($admin_resource = $this->getAdmin())
            return $admin_resource->profile->name;
        return null;
    }

    public function getAdmin()
    {
        if ($admin_resource = $this->admins()->first())
            return $admin_resource;
        return null;
    }

    public function getManagerMobile()
    {
        if ($operation_resource = $this->resources->where('pivot.resource_type', constants('RESOURCE_TYPES')['Operation'])->first()) {
            return $operation_resource->profile->mobile;
        } elseif ($admin_resource = $this->resources->where('pivot.resource_type', constants('RESOURCE_TYPES')['Admin'])->first()) {
            return $admin_resource->profile->mobile;
        }
        return null;
    }

    public function hasThisResource($resource_id, $type)
    {
        return $this->resources->where('id', (int)$resource_id)->where('pivot.resource_type', $type)->first() ? true : false;
    }

    public function transactions()
    {
        return $this->hasMany(PartnerTransaction::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function withdrawalRequests()
    {
        Relation::morphMap(['partner' => 'App\Models\Partner']);
        return $this->morphMany(WithdrawalRequest::class, 'requester');
    }

    public function lastWeekWithdrawalRequest()
    {
        return $this->withdrawalRequests()->lastWeek()->notCancelled()->first();
    }

    public function currentWeekWithdrawalRequest()
    {
        return $this->withdrawalRequests()->currentWeek()->notCancelled()->first();
    }

    public function onGoingJobs()
    {
        return $this->jobs()->whereIn('status', [
            constants('JOB_STATUSES')['Accepted'],
            constants('JOB_STATUSES')['Process'],
            constants('JOB_STATUSES')['Schedule_Due']
        ])->count();
    }
    public function resourcesInCategory($category)
    {
        $category             = $category instanceof Category ? $category->id : $category;
        $partner_resource_ids = [];
        $this->handymanResources()->verified()->get()->map(function ($resource) use (&$partner_resource_ids) {
            $partner_resource_ids[$resource->pivot->id] = $resource;
        });
        $result = [];
        collect(DB::table('category_partner_resource')->select('partner_resource_id')->whereIn('partner_resource_id', array_keys($partner_resource_ids))->where('category_id', $category)->get())->pluck('partner_resource_id')->each(function ($partner_resource_id) use ($partner_resource_ids, &$result) {
            $result[] = $partner_resource_ids[$partner_resource_id];
        });
        return collect($result);
    }

    public function handymanResources()
    {
        return $this->belongsToMany(Resource::class)->where('resource_type', constants('RESOURCE_TYPES')['Handyman'])->withPivot($this->resourcePivotColumns);
    }

    public function isCreditLimitExceed()
    {
        return !$this->hasAppropriateCreditLimit();
    }

    public function hasAppropriateCreditLimit()
    {
        return (double)$this->wallet >= (double)$this->walletSetting->min_wallet_threshold;
    }

    public function bankInformations()
    {
        return $this->hasOne(PartnerBankInformation::class);
    }

    public function affiliation()
    {
        return $this->belongsTo(PartnerAffiliation::class, 'affiliation_id');
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function totalWalletAmount()
    {
        return (double)$this->wallet + $this->bonusWallet();
    }

    public function bonusWallet()
    {
        return (double)$this->bonuses()->where('status', 'valid')->sum('amount');
    }

    public function bonusLogs()
    {
        return $this->morphMany(BonusLog::class, 'user');
    }

    public function subscription()
    {
        return $this->belongsTo(PartnerSubscriptionPackage::class, 'package_id');
    }

    public function subscriptionDiscount()
    {
        return $this->belongsTo(PartnerSubscriptionPackageDiscount::class, 'discount_id');
    }

    public function subscriptionOrders()
    {
        return $this->hasMany(SubscriptionOrder::class);
    }

    public function getSubscriptionRulesAttribute($rules)
    {
        return json_decode($rules);
    }

    public function subscribe($package, $billing_type)
    {
        $package     = $package ? (($package) instanceof PartnerSubscriptionPackage ? $package : PartnerSubscriptionPackage::find($package)) : $this->subscription;
        $discount    = $package->runningDiscount($billing_type);
        $discount_id = $discount ? $discount->id : null;
        $this->subscriber()->getPackage($package)->subscribe($billing_type, $discount_id);
    }

    public function subscriber()
    {
        return new PartnerSubscriber($this);
    }

    public function subscriptionUpgrade($package, $upgradeRequest = null)
    {
        $package = $package ? (($package) instanceof PartnerSubscriptionPackage ? $package : PartnerSubscriptionPackage::find($package)) : $this->subscription;
        $this->subscriber()->upgrade($package, $upgradeRequest);
    }

    public function getBonusCreditAttribute()
    {
        return (double)$this->bonuses()->valid()->sum('amount');
    }

    public function runSubscriptionBilling()
    {
        $this->subscriber()->getBilling()->runSubscriptionBilling();
    }

    public function runUpfrontSubscriptionBilling()
    {
        $this->subscriber()->getBilling()->runUpfrontBilling();
    }

    public function getCommissionAttribute()
    {
        return (double)$this->subscription_rules->commission->value;
    }

    public function canCreateResource(array $types)
    {
        return $this->subscriber()->canCreateResource($types);
    }

    public function subscriptionUpdateRequest()
    {
        return $this->hasMany(PartnerSubscriptionUpdateRequest::class);
    }

    public function canRequestForSubscriptionUpdate()
    {
        return !(PartnerSubscriptionUpdateRequest::status(constants('PARTNER_PACKAGE_UPDATE_STATUSES')['Pending'])->partner($this->id)->count());
    }

    public function lastSubscriptionUpdateRequest()
    {
        return PartnerSubscriptionUpdateRequest::status(constants('PARTNER_PACKAGE_UPDATE_STATUSES')['Pending'])->partner($this->id)->get()->last();
    }

    public function isFirstTimeVerified()
    {
        return $this->statusChangeLogs()->where('to', PartnerStatuses::VERIFIED)->count() == 0;
    }

    public function statusChangeLogs()
    {
        return $this->hasMany(PartnerStatusChangeLog::class);
    }

    public function impressionDeductions()
    {
        return $this->hasMany(ImpressionDeduction::class);
    }

    public function topUpTransaction(TopUpTransaction $transaction)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->debitWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Debit', 'log' => $transaction->getLog()]);*/
        (new WalletTransactionHandler())->setModel($this)->setAmount($transaction->getAmount())->setSource(TransactionSources::TOP_UP)->setType('debit')->setLog($transaction->getLog())->dispatch();
    }

    public function todayJobs($jobs = null)
    {
        if (is_null($jobs)) {
            return $this->notCancelledJobs()->filter(function ($job, $key) {
                return $job->schedule_date == Carbon::now()->toDateString() && !in_array($job->status, [
                        'Served',
                        'Cancelled',
                        'Declined',
                        'Not Responded'
                    ]);
            });
        }
        return $jobs->filter(function ($job, $key) {
            return $job->schedule_date == Carbon::now()->toDateString() && !in_array($job->status, [
                    'Served',
                    'Cancelled',
                    'Declined',
                    'Not Responded'
                ]);
        });
    }

    public function notCancelledJobs()
    {
        /*return $this->jobs->reject(function ($job) {
            return $job->cancelRequests()->count() > 0;
        });*/
        return $this->jobs()->whereNotExists(function ($q) {
            $q->from('job_cancel_requests')->whereRaw('job_id = jobs.id');
        })->select('jobs.id', 'schedule_date', 'status')->get();

    }

    public function tomorrowJobs($jobs = null)
    {
        if (is_null($jobs)) {
            return $this->notCancelledJobs()->filter(function ($job, $key) {
                return $job->schedule_date == Carbon::tomorrow()->toDateString() && !in_array($job->status, [
                        'Served',
                        'Cancelled',
                        'Declined'
                    ]);
            });
        }
        return $jobs->filter(function ($job, $key) {
            return $job->schedule_date == Carbon::tomorrow()->toDateString() && !in_array($job->status, [
                    'Served',
                    'Cancelled',
                    'Declined'
                ]);
        });
    }

    public function notRespondedJobs($jobs = null)
    {
        if (is_null($jobs))
            return $this->notCancelledJobs()->where('status', constants('JOB_STATUSES')['Not_Responded']);
        return $jobs->where('status', constants('JOB_STATUSES')['Not_Responded']);
    }

    public function scheduleDueJobs($jobs = null)
    {
        if (is_null($jobs))
            return $this->notCancelledJobs()->where('status', constants('JOB_STATUSES')['Schedule_Due']);
        return $jobs->where('status', constants('JOB_STATUSES')['Schedule_Due']);
    }

    public function serveDueJobs($jobs = null)
    {
        if (is_null($jobs))
            return $this->notCancelledJobs()->where('status', constants('JOB_STATUSES')['Serve_Due']);
        return $jobs->where('status', constants('JOB_STATUSES')['Serve_Due']);
    }

    public function getCommission()
    {
        return new \Sheba\TopUp\Commission\Partner();
    }

    public function getHyperLocation()
    {
        $geo = json_decode($this->geo_informations);
        return HyperLocal::insidePolygon($geo->lat, $geo->lng)->first();
    }

    public function hasCoverageOn(Coords $coords)
    {
        $geo           = json_decode($this->geo_informations);
        $partner_coord = new Coords(floatval($geo->lat), floatval($geo->lng));
        $distance      = (new Distance(DistanceStrategy::$VINCENTY))->linear();
        return $distance->to($coords)->from($partner_coord)->isWithin($geo->radius * 1000);
    }

    public function geoChangeLogs()
    {
        return $this->hasMany(PartnerGeoChangeLog::class);
    }

    public function isLite()
    {
        return $this->package_id == (int)config('sheba.partner_lite_packages_id');
    }

    public function isAccessibleForMarketPlace()
    {
        return !in_array($this->package_id, config('sheba.marketplace_not_accessible_packages_id'));
    }

    public function scopeLite($q)
    {
        return $q->where('package_id', (int)config('sheba.partner_lite_packages_id'));
    }

    public function scopeModerated($query)
    {
        return $query->where('moderator_id', '<>', null)->where('moderation_status', 'approved');
    }

    public function servingMasterCategories()
    {
        $this->load([
            'categories' => function ($q) {
                $q->with([
                    'parent' => function ($q) {
                        $q->select('id', 'categories.name');
                    }
                ]);
            }
        ]);
        return $this->categories->pluck('parent.name')->unique()->implode(', ');
    }

    public function servingMasterCategoryIds()
    {
        return array_unique($this->categories->pluck('parent_id')->toArray());
    }

    public function resolveBadge()
    {
        return (new BadgeResolver())->setPartner($this)->resolveVersionWiseBadge()->getBadge();
    }

    public function resolveSubscriptionType()
    {
        return (new BadgeResolver())->setPartner($this)->resolveVersionWiseBadge()->getSubscriptionType();
    }

    public function getTopFiveResources()
    {
        return $this->resources()->reviews()->groupBy('resource_id')->orderBy('avg(reviews.rating)')->select('id, avg(reviews.rating)')->get();
    }

    public function resources()
    {
        return $this->belongsToMany(Resource::class)->withPivot($this->resourcePivotColumns);
    }

    public function businessAdditionalInformation()
    {
        return json_decode($this->business_additional_information);
    }

    public function salesInformation()
    {
        return json_decode($this->sales_information);
    }

    public function posServices()
    {
        return $this->hasMany(PartnerPosService::class);
    }

    public function posOrders()
    {
        return $this->hasMany(PosOrder::class);
    }

    public function posSetting()
    {
        return $this->hasOne(PartnerPosSetting::class);
    }

    public function getMobile()
    {
        return $this->getContactNumber();
    }

    public function isAllowedToSendWithdrawalRequest()
    {
        return !($this->withdrawalRequests()->active()->count() > 0);
    }

    /**
     * @return BusTicketCommission|\Sheba\Transport\Bus\Commission\Partner
     */
    public function getBusTicketCommission()
    {
        return new \Sheba\Transport\Bus\Commission\Partner();
    }

    public function transportTicketTransaction(TransportTicketTransaction $transaction)
    {
        (new WalletTransactionHandler())->setModel($this)->setAmount($transaction->getAmount())->setSource(TransactionSources::TRANSPORT)->setType('credit')->setLog($transaction->getLog())->dispatch();
    }

    public function transportTicketOrders()
    {
        return $this->morphMany(TransportTicketOrder::class, 'agent');
    }

    public function movieTicketTransaction(MovieTicketTransaction $transaction)
    {
        (new WalletTransactionHandler())->setModel($this)->setAmount($transaction->getAmount())->setSource(TransactionSources::MOVIE)->setType('debit')->setLog($transaction->getLog())->dispatch();
    }

    public function movieTicketTransactionNew(MovieTicketTransaction $transaction)
    {
        (new WalletTransactionHandler())->setModel($this)->setAmount($transaction->getAmount())->setSource(TransactionSources::MOVIE)->setType('credit')->setLog($transaction->getLog())->dispatch();
    }

    public function getMovieTicketCommission()
    {
        return new \Sheba\MovieTicket\Commission\Partner();
    }

    /**
     * @param PartnerSubscriptionPackage $package
     * @param $billingType
     * @param int $billingCycle
     * @return bool
     */
    public function hasCreditForSubscription(PartnerSubscriptionPackage $package, $billingType, $billingCycle = 1)
    {
        $this->totalPriceRequiredForSubscription = $package->originalPrice($billingType) - (double)$package->discountPrice($billingType, $billingCycle);
        $this->totalCreditForSubscription        = $this->getTotalCreditExistsForSubscription();
        return $this->totalCreditForSubscription >= $this->totalPriceRequiredForSubscription;
    }

    /** @return float|int */
    public function getTotalCreditExistsForSubscription()
    {
        list($remaining, $wallet, $bonus_wallet, $threshold) = $this->getCreditBreakdown();
        return round($bonus_wallet + $wallet + $remaining) - $threshold;
    }

    /** @return array */
    public function getCreditBreakdown()
    {
        $remaining             = (double)$this->subscriber()->getBilling()->remainingCredit($this->subscription, $this->billing_type);
        $wallet                = (double)$this->wallet;
        $bonus_wallet          = (double)$this->bonusWallet();
        $threshold             = $this->walletSetting ? (double)$this->walletSetting->min_wallet_threshold : 0;
        $this->creditBreakdown = [
            'remaining_subscription_charge' => $remaining,
            'wallet'                        => $wallet,
            'threshold'                     => $threshold,
            'bonus_wallet'                  => $bonus_wallet
        ];
        return [
            $remaining,
            $wallet,
            $bonus_wallet,
            $threshold
        ];
    }

    public function alreadyCollectedSubscriptionFee()
    {
        if (!$this->isAlreadyCollectedAdvanceSubscriptionFee())
            return 0;
        $last_subscription_package_charge = $this->subscriptionPackageCharges()->orderBy('id', 'desc')->first();
        if (!empty($last_subscription_package_charge))
            return $last_subscription_package_charge->package_price; else return 0;
    }

    /**
     * @return bool
     */
    public function isAlreadyCollectedAdvanceSubscriptionFee()
    {
        $last_subscription_package_charge = $this->subscriptionPackageCharges()->orderBy('id', 'desc')->first();
        if (empty($last_subscription_package_charge))
            return false;
        return $this->last_billed_date ? $last_subscription_package_charge->billing_date->between($this->last_billed_date->addSecond(), $this->periodicBillingHandler()->nextBillingDate()) : false;
    }

    public function subscriptionPackageCharges()
    {
        return $this->hasMany(PartnerSubscriptionPackageCharge::class);
    }

    public function periodicBillingHandler()
    {
        return $this->subscriber()->periodicBillingHandler();
    }

    public function getStatusToCalculateAccess()
    {
        return PartnerStatuses::getStatusToCalculateAccess($this->status);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(PartnerReferral::class, 'partner_id', 'id');
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'referrer_id', 'id');
    }

    public function usage(): HasMany
    {
        return $this->hasMany(PartnerUsageHistory::class, 'partner_id', 'id');
    }

    public function referCode()
    {
        return $this->id . str_random(8 - (strlen($this->id)));
    }
}
