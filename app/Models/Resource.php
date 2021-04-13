<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sheba\Dal\ArtisanLeave\ArtisanLeave;
use Sheba\Dal\BaseModel;
use Sheba\Dal\ResourceStatusChangeLog\Model;
use Sheba\Dal\ResourceTransaction\Model as ResourceTransaction;
use Sheba\Dal\Retailer\Retailer;
use Sheba\Wallet\Wallet;
use Sheba\Reward\Rewardable;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Illuminate\Database\Eloquent\Relations\Relation;
use Sheba\Dal\Category\Category;

class Resource extends BaseModel implements Rewardable, HasWalletTransaction
{
    use Wallet;

    protected $guarded = ['id'];
    protected $casts = ['wallet' => 'double'];
    /**
     * @var bool|\Carbon\Carbon|float|\Illuminate\Support\Collection|int|mixed|string|null
     */
    private $remember_token;

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function statusChangeLog()
    {
        return $this->hasMany(Model::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class, 'profile_id', 'profile_id');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function transactions()
    {
        return $this->hasMany(ResourceTransaction::class);
    }

    public function associatePartners()
    {
        return $this->partners->unique();
    }

    public function firstPartner()
    {
        return $this->associatePartners()->first();
    }

    public function partnerResources()
    {
        return $this->hasMany(PartnerResource::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * @return HasMany
     */
    public function retailers()
    {
        /** @var Profile $profile */
        $profile = $this->profile;
        return $profile->retailers();
    }

    public function withdrawalRequests()
    {
        Relation::morphMap(['resource' => 'App\Models\Resource']);
        return $this->morphMany(WithdrawalRequest::class, 'requester');
    }

    public function typeIn($partner)
    {
        $partner = $partner instanceof Partner ? $partner->id : $partner;
        $types = [];
        foreach ($this->partners()->withPivot('resource_type')->where('partner_id', $partner)->get() as $unique_partner) {
            $types[] = $unique_partner->pivot->resource_type;
        }
        return $types;
    }

    public function isOfTypesIn(Partner $partner, $types)
    {
        return boolval(count(array_intersect($types, $this->typeIn($partner))));
    }

    public function isManager(Partner $partner)
    {
        return $this->isOfTypesIn($partner, ["Admin", "Operation", "Owner", "Management", "Finance", "Salesman"]);
    }

    public function isAdmin(Partner $partner)
    {
        return $this->isOfTypesIn($partner, ["Admin", "Owner"]);
    }

    public function categoriesIn($partner)
    {
        $partner = $partner instanceof Partner ? $partner->id : $partner;
        $categories = collect();
        $partner_resources = ($this->partnerResources()->where('partner_id', $partner)->get())->load('categories');
        foreach ($partner_resources as $partner_resource) {
            foreach ($partner_resource->categories as $item) {
                $categories->push($item);
            }
        }
        return $categories->unique('id');
    }

    public function scopeVerified($query)
    {
        return $query->where('resources.is_verified', 1);
    }

    public function scopeType($query, $type)
    {
        return $query->where('resource_type', $type);
    }

    public function resourceSchedules()
    {
        return $this->hasMany(ResourceSchedule::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function totalServedJobs()
    {
        return $this->jobs->filter(function ($job) {
            return $job->status === 'Served';
        })->count();
    }

    public function totalJobs()
    {
        return $this->jobs->count();
    }

    public function totalWalletAmount()
    {
        return $this->wallet;
    }

    public function isAllowedToSendWithdrawalRequest()
    {
        return !($this->withdrawalRequests()->active()->count() > 0);
    }

    public function isAllowedForMicroLoan()
    {
        return $this->retailers->count() > 0;
    }

    public function leaves()
    {
        Relation::morphMap(['resource' => 'App\Models\Resource']);
        return $this->morphMany(ArtisanLeave::class, 'artisan');
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
}
