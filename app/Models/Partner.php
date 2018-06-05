<?php namespace App\Models;

use Carbon\Carbon;
use Sheba\Dal\Complain\Model as Complain;
use Illuminate\Database\Eloquent\Model;
use Sheba\Voucher\VoucherCodeGenerator;
use DB;

class Partner extends Model
{
    protected $guarded = [
        'id',
    ];
    protected $casts = ['wallet' => 'double'];

    protected $resourcePivotColumns = ['id', 'designation', 'department', 'resource_type', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];
    protected $categoryPivotColumns = ['id', 'experience', 'preparation_time_minutes','response_time_min', 'response_time_max', 'commission', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];
    protected $servicePivotColumns = ['id', 'description', 'options', 'prices', 'is_published', 'discount', 'discount_start_date', 'discount_start_date', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];

    public function basicInformations()
    {
        return $this->hasOne(PartnerBasicInformation::class);
    }

    public function admins()
    {
        return $this->belongsToMany(Resource::class)
            ->where('resource_type', constants('RESOURCE_TYPES')['Admin'])
            ->withPivot($this->resourcePivotColumns);
    }

    public function operationResources()
    {
        return $this->belongsToMany(Resource::class)
            ->where('resource_type', constants('RESOURCE_TYPES')['Operation'])
            ->withPivot($this->resourcePivotColumns);
    }

    public function financeResources()
    {
        return $this->belongsToMany(Resource::class)
            ->where('resource_type', constants('RESOURCE_TYPES')['Finance'])
            ->withPivot($this->resourcePivotColumns);
    }

    public function handymanResources()
    {
        return $this->belongsToMany(Resource::class)
            ->where('resource_type', constants('RESOURCE_TYPES')['Handyman'])
            ->withPivot($this->resourcePivotColumns);
    }

    public function resources()
    {
        return $this->belongsToMany(Resource::class)->withPivot($this->resourcePivotColumns);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withPivot($this->categoryPivotColumns);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)->withPivot($this->servicePivotColumns);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
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

    public function commission($service_id)
    {
        $service_category = Service::find($service_id)->category->id;
        return $this->categories()->find($service_category)->pivot->commission;
    }

    public function leaves()
    {
        return $this->hasMany(PartnerLeave::class);
    }

    public function runningLeave($date = null)
    {
        $date = ($date) ? (($date instanceof Carbon) ? $date : new Carbon($date)) : Carbon::now();
        foreach ($this->leaves()->whereDate('start', '<=', $date)->get() as $leave) {
            if ($leave->isRunning($date)) return $leave;
        }
        return null;
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

    public function generateReferral()
    {
        return VoucherCodeGenerator::byName($this->name);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'Verified');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'Verified');
    }

    public function getContactNumber()
    {
        if ($operation_resource = $this->operationResources()->first())
            return $operation_resource->profile->mobile;
        if ($admin_resource = $this->admins()->first())
            return $admin_resource->profile->mobile;
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
        return $this->hasMany(PartnerWithdrawalRequest::class);
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
        return $this->jobs()->whereIn('status', [constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Schedule_Due']])->count();
    }

    public function resourcesInCategory($category)
    {
        $category = $category instanceof Category ? $category->id : $category;
        $partner_resource_ids = [];
        $this->handymanResources->map(function ($resource) use (&$partner_resource_ids) {
            $partner_resource_ids[$resource->pivot->id] = $resource;
        });

        $result = [];

        collect(
            DB::table('category_partner_resource')->select('partner_resource_id')
                ->whereIn('partner_resource_id', array_keys($partner_resource_ids))
                ->where('category_id', $category)
                ->get()
        )->pluck('partner_resource_id')->each(function ($partner_resource_id) use ($partner_resource_ids, &$result) {
            $result[] = $partner_resource_ids[$partner_resource_id];
        });

        return collect($result);
    }

    public function isCreditLimitExceed()
    {
        return (double)$this->wallet < (double)$this->walletSetting->min_wallet_threshold;
    }
    public function bankInformations()
    {
        return $this->hasOne(PartnerBankInformation::class);
    }
}
