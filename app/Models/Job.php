<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\CiCalculator;
use Sheba\Dal\Complain\Model as Complain;

class Job extends Model
{
    protected $materialPivotColumns = ['id', 'material_name', 'material_price', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];
    protected $guarded = ['id'];
    protected $casts = ['sheba_contribution' => 'double', 'partner_contribution' => 'double', 'commission_rate' => 'double'];
    protected $dates = ['delivered_date'];
    public $servicePrice;
    public $commissionRate;
    public $serviceCost;
    public $materialPrice;
    public $materialCost;
    public $grossCost;
    public $totalPriceWithoutVat;
    public $totalPrice;
    public $totalCost;
    public $totalCostWithoutDiscount;
    public $grossPrice;
    public $discountContributionSheba;
    public $discountContributionPartner;
    public $profit;
    public $margin;
    public $isCalculated;
    public $ownDiscount;
    public $ownShebaContribution;
    public $ownPartnerContribution;
    public $serviceDiscounts;
    public $ownDiscountContributionSheba;
    public $ownDiscountContributionPartner;
    public $serviceDiscountContributionSheba;
    public $serviceDiscountContributionPartner;
    public $complexityIndex;
    public $isInWarranty;

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function jobServices()
    {
        return $this->hasMany(JobService::class);
    }

    public function carRentalJobDetail()
    {
        return $this->hasOne(CarRentalJobDetail::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class)->withPivot($this->materialPivotColumns);
    }

    public function usedMaterials()
    {
        return $this->hasMany(JobMaterial::class);
    }

    public function partner_order()
    {
        return $this->belongsTo(PartnerOrder::class);
    }

    public function partnerOrder()
    {
        return $this->belongsTo(PartnerOrder::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function customerReview()
    {
        return $this->hasOne(CustomerReview::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function calculate($price_only = false)
    {
        //$this->commissionRate = $this->partnerOrder->partner->categories()->find($this->service->category->id)->pivot->commission;
        $costRate = 1 - ($this->commission_rate / 100);

        $this->servicePrice = formatTaka(($this->service_unit_price * $this->service_quantity) + $this->calculateServicePrice());
        $this->serviceCost = formatTaka($this->servicePrice * $costRate);
        $this->materialPrice = formatTaka($this->calculateMaterialPrice());
        $this->materialCost = formatTaka($this->materialPrice * $costRate);
        $this->totalCostWithoutDiscount = formatTaka($this->serviceCost + $this->materialCost);
        $this->totalPriceWithoutVat = formatTaka($this->servicePrice + $this->materialPrice + $this->delivery_charge);
        //$this->totalPrice = formatTaka($this->totalPriceWithoutVat + $this->vat); // later
        $this->totalPrice = formatTaka($this->totalPriceWithoutVat);
        $this_discount = $this->isCalculated ? Job::find($this->id)->discount : $this->discount;
        $this->ownDiscount = $this_discount;
        $this->ownShebaContribution = $this->sheba_contribution;
        $this->ownPartnerContribution = $this->partner_contribution;
        $this->serviceDiscounts = $this->calculateServiceDiscount();
        $this->discount = $this_discount + $this->serviceDiscounts;
        $this->grossPrice = ($this->totalPrice > $this->discount) ? formatTaka($this->totalPrice - $this->discount) : 0;
        $this->service_unit_price = formatTaka($this->service_unit_price);
        $this->ownDiscountContributionSheba = formatTaka(($this->ownDiscount * $this->ownShebaContribution) / 100);
        $this->ownDiscountContributionPartner = formatTaka(($this->ownDiscount * $this->ownPartnerContribution) / 100);
        $this->serviceDiscountContributionSheba = $this->calculateServiceDiscountContributionSheba();
        $this->serviceDiscountContributionPartner = $this->calculateServiceDiscountContributionPartner();
        $this->discountContributionSheba = formatTaka($this->ownDiscountContributionSheba + $this->serviceDiscountContributionSheba);
        $this->discountContributionPartner = formatTaka($this->ownDiscountContributionPartner + $this->serviceDiscountContributionPartner);
        $this->totalCost = $this->totalCostWithoutDiscount - $this->discountContributionPartner;
        $this->grossCost = formatTaka($this->totalCost);
        $this->profit = formatTaka($this->grossPrice - $this->totalCost);
        $this->margin = ($this->totalPrice != 0) ? (($this->grossPrice - $this->totalCost) * 100) / $this->totalPrice : 0;
        $this->margin = formatTaka($this->margin);
        if (!$price_only) {
            $this->calculateComplexityIndex();
        }
        $this->isInWarranty = $this->isInWarranty();
        $this->isCalculated = true;
        return $this;
    }

    private function calculateServicePrice()
    {
        $total_service_price = 0;
        foreach ($this->jobServices as $jobService) {
            $total_service_price += ($jobService->min_price > ($jobService->unit_price * $jobService->quantity) ? $jobService->min_price : ($jobService->unit_price * $jobService->quantity));
        }
        return $total_service_price;
    }

    private function calculateServiceDiscount()
    {
        $total_discount_price = 0;
        foreach ($this->jobServices as $jobService) {
            $total_discount_price += $jobService->discount;
        }
        return $total_discount_price;
    }

    private function calculateServiceDiscountContributionSheba()
    {
        $total_sheba_discount_contribution_price = 0;
        foreach ($this->jobServices as $jobService) {
            $total_sheba_discount_contribution_price += $jobService->discount * $jobService->sheba_contribution;
        }
        return $total_sheba_discount_contribution_price / 100;
    }

    private function calculateServiceDiscountContributionPartner()
    {
        $total_partner_discount_contribution_price = 0;
        foreach ($this->jobServices as $jobService) {
            $total_partner_discount_contribution_price += $jobService->discount * $jobService->partner_contribution;
        }
        return $total_partner_discount_contribution_price / 100;
    }

    public function isInWarranty()
    {
        if ($this->status != $this->jobStatuses["Served"] || !$this->delivered_date) return false;
        return Carbon::now()->between($this->delivered_date, $this->delivered_date->addDays($this->warranty));
    }

    public function calculateComplexityIndex()
    {
        $this->complexityIndex = (new CiCalculator($this))->calculate();
        return $this;
    }

    public function getCategoryAnswersAttribute($category_answers)
    {
        return json_decode($category_answers, true);
    }

    /**
     * @return mixed
     */
    private function calculateMaterialPrice()
    {
        $total_material_price = 0;
        foreach ($this->usedMaterials as $used_material) {
            $total_material_price += $used_material->material_price;
        }
        return $total_material_price;
    }

    public function code()
    {
        $startFrom = 16000;
        return sprintf('%08d', $this->id + $startFrom);
    }

    public function fullCode()
    {
        return $this->partner_order->code() . '-' . $this->code();
    }

    public function cancelLog()
    {
        return $this->hasOne(JobCancelLog::class);
    }

    public function partnerChangeLog()
    {
        return $this->hasOne(JobPartnerChangeLog::class);
    }

    public function statusChangeLogs()
    {
        return $this->hasMany(JobStatusChangeLog::class);
    }

    public function updateLogs()
    {
        return $this->hasMany(JobUpdateLog::class);
    }

    public function scopeInfo($query)
    {
        return $query->select(
            'jobs.id', 'jobs.discount', 'jobs.created_at', 'jobs.category_id', 'sheba_contribution', 'jobs.preferred_time_start',
            'partner_contribution', 'commission_rate', 'resource_id', 'schedule_date', 'service_variables',
            'job_additional_info', 'delivered_date', 'preferred_time', 'service_name',
            'status', 'site', 'service_quantity', 'service_unit_price', 'service_id', 'partner_order_id'
        );
    }

    public function scopeInfoV2($query)
    {
        return $query->select('jobs.id', 'jobs.created_at', 'resource_id', 'schedule_date', 'preferred_time', 'status', 'partner_order_id', 'resource_id');
    }

    public function scopeValidStatus($query)
    {
        return $query->whereIn('status', ['Accepted', 'Served', 'Process', 'Schedule Due', 'Serve Due']);
    }

    public function scopeTillNow($query)
    {
        return $query->where('schedule_date', '<=', date('Y-m-d'));
    }

    public function scopeStatus($query, Array $status)
    {
        return $query->whereIn('status', $status);
    }

    public function materialLogs()
    {
        return $this->hasMany(JobMaterialLog::class);
    }

    public function complains()
    {
        return $this->hasMany(Complain::class);
    }

    public function customerComplains()
    {
        return $this->complains()->whereHas('accessor', function ($query) {
            $query->where('model_name', Customer::class);
        });
    }

    public function cancelRequests()
    {
        return $this->hasMany(JobCancelRequest::class);
    }

    public function hasStatus(Array $status)
    {
        foreach ($status as $key => $value) {
            $status[$key] = constants('JOB_STATUSES')[$value];
        }
        return in_array($this->status, $status);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getVersion()
    {
        return $this->partner_order_id > env('LAST_PARTNER_ORDER_ID_V1') ? 'v2' : 'v1';
    }

    public function department()
    {
        return $this->partnerOrder->order->department();
    }

    public function resourceSchedule()
    {
        return $this->hasOne(ResourceSchedule::class);
    }

    public function resourceScheduleSlot()
    {
        return $this->hasMany(ResourceSchedule::class);
    }

    public function getReadablePreferredTimeAttribute()
    {
        if ($this->preferred_time !== 'Anytime') {
            $time = explode('-', $this->preferred_time);
            return (Carbon::parse($time[0]))->format('g:i A') . '-' . (Carbon::parse($time[1]))->format('g:i A');
        }
        return $this->preferred_time;
    }

    public function isRentCar()
    {
        return in_array($this->category_id, array_map('intval', explode(',', env('RENT_CAR_IDS')))) ? 1 : 0;
    }

    public function isClosed()
    {
        return in_array($this->status, ["Served", "Cancelled"]);
    }

    public function isCancelRequestPending()
    {
        if ($cancel_request = $this->cancelRequests->last())
            return $cancel_request->status == constants('CANCEL_REQUEST_STATUSES')['Pending'];

        return false;
    }

    public function scopeOngoing($query)
    {
        return $query->whereIn('status',
            array(
                constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Schedule_Due'],
                constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Serve_Due'],
                constants('JOB_STATUSES')['Served']
            )
        );
    }

    public function canCallExpert()
    {
        if (in_array($this->status, ['Accepted', 'Schedule Due', 'Process', 'Serve Due', 'Served'])) return Carbon::today()->gte(Carbon::parse($this->schedule_date));
        else return false;
    }

    public function isOnPremise()
    {
        return $this->site == constants('JOB_ON_PREMISE')['partner'] ? 1 : 0;
    }
}
