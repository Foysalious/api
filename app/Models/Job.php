<?php namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Sheba\CancelRequest\CancelRequestStatuses;
use Sheba\Comment\JobNotificationHandler;
use Sheba\Comment\MorphCommentable;
use Sheba\Comment\MorphComments;
use Sheba\Dal\BaseModel;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\Job\Events\JobSaved;
use Sheba\Dal\JobDiscount\JobDiscount;
use Sheba\Dal\JobCancelLog\JobCancelLog;
use Sheba\Dal\JobMaterial\JobMaterial;
use Sheba\Dal\JobService\JobService;
use Sheba\Dal\JobCancelRequest\JobCancelRequest;
use Sheba\Helpers\TimeFrame;
use Sheba\Jobs\CiCalculator;
use Sheba\Dal\Complain\Model as Complain;
use Sheba\Jobs\JobStatuses;
use Sheba\Jobs\PreferredTime;
use Sheba\Jobs\Premises;
use Sheba\Logistics\Literals\Natures as LogisticNatures;
use Sheba\Logistics\Literals\OneWayInitEvents as OneWayLogisticInitEvents;
use Sheba\Logistics\OrderManager;
use Sheba\Logistics\Repository\ParcelRepository;
use Sheba\Order\Code\Builder as CodeBuilder;
use Sheba\Dal\JobUpdateLog\JobUpdateLog;
use Sheba\Dal\JobMaterialLog\JobMaterialLog;
use Sheba\Dal\JobScheduleDueLog\JobScheduleDueLog;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use Sheba\Dal\JobPartnerChangeLog\JobPartnerChangeLog;
use Sheba\Dal\JobStatusChangeLog\JobStatusChangeLog;
use Sheba\Dal\Category\Category;
use Sheba\Dal\Service\Service;

class Job extends BaseModel implements MorphCommentable
{
    use MorphComments;

    protected $guarded = ['id'];
    protected $materialPivotColumns = ['id', 'material_name', 'material_price', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];
    protected $casts = ['sheba_contribution' => 'double', 'vendor_contribution' => 'double', 'partner_contribution' => 'double', 'commission_rate' => 'double'];
    protected $dates = ['delivered_date', 'estimated_delivery_date', 'estimated_visiting_date'];

    public $servicePrice;
    public $totalServiceSurcharge;
    public $serviceCost;
    public $serviceCostRate;
    public $materialPrice;
    public $materialCostRate;
    public $materialCost;
    public $deliveryPrice;
    public $deliveryCostRate;
    public $deliveryCost;
    public $logisticProfit;
    public $logisticCostRateForPartner;
    public $logisticCostForPartner;
    public $logisticSystemChargeRate;
    public $logisticSystemCharge;
    public $grossCost;
    public $totalPriceWithoutVat;
    public $totalPrice;
    public $totalCost;
    public $commission;
    public $totalCostWithoutDiscount;
    public $grossPrice;
    public $ownDiscount;
    public $ownShebaContribution;
    public $ownPartnerContribution;
    public $ownVendorContribution;
    public $serviceDiscounts;
    public $originalDiscount;
    public $totalDiscount;
    public $totalDiscountWithoutOtherDiscounts;
    public $discountByPromo;
    public $discountContributionSheba;
    public $discountContributionPartner;
    public $ownDiscountContributionSheba;
    public $ownDiscountContributionPartner;
    public $serviceDiscountContributionSheba;
    public $serviceDiscountContributionPartner;
    public $otherDiscounts;
    public $otherDiscountContributionSheba;
    public $otherDiscountContributionPartner;
    public $otherDiscountsByType = [];
    public $otherDiscountContributionShebaByType = [];
    public $otherDiscountContributionPartnerByType = [];
    public $profit;
    public $margin;
    public $deliveryDiscount = 0.00;
    public $deliveryDiscountPartnerContribution;
    public $grossLogisticCharge;
    public $logisticDue;
    public $complexityIndex;
    public $isInWarranty;
    public $isCalculated;
    public $servicePriceWithoutPartnerContribution;
    public $materialPriceWithoutPartnerContribution;
    public $deliveryPriceWithoutPartnerContribution;
    /** @var float|int */
    public $discountWithoutDeliveryDiscount;
    public $logisticDueWithoutDiscount;
    /** @var \Sheba\Logistics\DTO\Order */
    private $currentLogisticOrder;
    /** @var \Sheba\Logistics\DTO\Order */
    private $firstLogisticOrder;
    /** @var \Sheba\Logistics\DTO\Order */
    private $lastLogisticOrder;

    /** @var CodeBuilder */
    private $codeBuilder;
    /** @var OrderManager */
    private $logisticOrderManager;

    public static $savedEventClass = JobSaved::class;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->codeBuilder = new CodeBuilder();
        $this->logisticOrderManager = app(OrderManager::class);
    }

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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
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

    public function materialLogs()
    {
        return $this->hasMany(JobMaterialLog::class);
    }

    public function complains()
    {
        return $this->hasMany(Complain::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tasks()
    {
        return $this->morphMany(ToDoTask::class, 'focused_to');
    }

    public function mentions()
    {
        return $this->morphMany(Mention::class, 'mentionable');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
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

    public function customerFavorite()
    {
        return $this->hasOne(CustomerFavorite::class);
    }

    public function crm()
    {
        return $this->belongsTo(User::class, 'crm_id');
    }

    public function cancelLog()
    {
        return $this->hasOne(JobCancelLog::class);
    }

    public function cancelRequest()
    {
        return $this->hasMany(JobCancelRequest::class);
    }

    public function pendingCancelRequests()
    {
        return $this->cancelRequest()->where('status', CancelRequestStatuses::PENDING);
    }

    public function partnerChangeLog()
    {
        return $this->hasOne(JobPartnerChangeLog::class);
    }

    public function statusChangeLogs()
    {
        return $this->hasMany(JobStatusChangeLog::class);
    }

    public function cmChangeLog()
    {
        return $this->hasMany(JobCrmChangeLog::class);
    }

    public function updateLogs()
    {
        return $this->hasMany(JobUpdateLog::class);
    }

    public function discounts()
    {
        return $this->hasMany(JobDiscount::class);
    }

    public function resourceSchedule()
    {
        return $this->hasOne(ResourceSchedule::class);
    }

    public function resourceScheduleSlot()
    {
        return $this->hasMany(ResourceSchedule::class);
    }

    public function scheduleDueLog()
    {
        return $this->hasMany(JobScheduleDueLog::class);
    }

    public function cancelRequests()
    {
        return $this->hasMany(JobCancelRequest::class);
    }

    /*public function setScheduleDateAttribute($date)
    {
        $this->attributes['schedule_date'] = Carbon::parse($date);
    }

    public function getScheduleDateAttribute($date)
    {
        return (new Carbon($date))->format('Y-m-d');
    }*/

    /**
     * @param bool $price_only
     * @return $this
     */
    public function calculate($price_only = false)
    {
        $is_old_order = $this->id < config('sheba.last_job_before_commission');
        /**
         * CALCULATING COMMISSION RATES
         */
        $this->serviceCostRate = 1 - ($this->commission_rate / 100);
        $this->materialCostRate = 1 - ($this->material_commission_rate / 100);
        $this->deliveryCostRate = 1 - ($this->delivery_commission_rate / 100);
        $this->logisticCostRateForPartner = $this->delivery_commission_rate / 100;
        $this->logisticSystemChargeRate = 0;

        /**
         * CALCULATING THE PRICES
         */
        $this->servicePrice = formatTaka(($this->service_unit_price * $this->service_quantity) + $this->getServicePrice());
        $this->materialPrice = formatTaka($this->calculateMaterialPrice());
        $this->deliveryPrice = floatval($this->delivery_charge) ?: floatval($this->logistic_charge);
        $this->totalPriceWithoutVat = formatTaka($this->servicePrice + $this->materialPrice + $this->delivery_charge);
        $this->logisticSystemCharge = $this->logistic_charge * $this->logisticSystemChargeRate;
        $this->logisticProfit = $this->logisticCostForPartner - $this->logisticSystemCharge;
        //$this->totalPrice = formatTaka($this->totalPriceWithoutVat + $this->vat); // later
        $this->totalPrice = $this->totalPriceWithoutVat;
        $this->totalPrice = formatTaka($this->totalPriceWithoutVat);

        /**
         * CALCULATING DISCOUNT
         */
        $this->calculateOtherDiscounts();
        $this_discount = $this->isCalculated ? Job::find($this->id)->discount : $this->discount;
        $this->ownDiscount = $this_discount - $this->otherDiscounts;
        $this->ownShebaContribution = $this->sheba_contribution;
        $this->ownPartnerContribution = $this->partner_contribution;
        $this->ownVendorContribution = $this->vendor_contribution;
        $this->serviceDiscounts = $this->getServiceDiscount();

//        CHANGED FOR MULTIPLE DISCOUNT PLACED ERROR
//        $this->discountByPromo = $this->discount - $this->otherDiscounts;
//        $this->totalDiscount = $this->discount = $this_discount + $this->serviceDiscounts;

        $this->discountByPromo = $this->serviceDiscounts ? 0 : $this->discount - $this->otherDiscounts;
        $this->totalDiscount = $this->discount = $this->serviceDiscounts ? $this->serviceDiscounts : $this_discount + $this->serviceDiscounts;
        $this->totalDiscountWithoutOtherDiscounts = $this->totalDiscount - $this->otherDiscounts;
        $this->originalDiscount = $this->isCapApplied() ? 0 : $this->original_discount_amount + $this->serviceDiscounts;
        $this->grossPrice = ($this->totalPrice > $this->discount) ? formatTaka($this->totalPrice - $this->discount) : 0;
        $this->service_unit_price = formatTaka($this->service_unit_price);

        /**
         * CALCULATING CONTRIBUTION
         */
        $this->ownDiscountContributionSheba = formatTaka(($this->ownDiscount * $this->ownShebaContribution) / 100);
        $this->ownDiscountContributionPartner = formatTaka(($this->ownDiscount * $this->ownPartnerContribution) / 100);
        $this->serviceDiscountContributionSheba = $this->getServiceDiscountContributionSheba();
        $this->serviceDiscountContributionPartner = $this->getServiceDiscountContributionPartner();
        $this->discountContributionSheba = formatTaka($this->ownDiscountContributionSheba + $this->serviceDiscountContributionSheba + $this->otherDiscountContributionSheba);
        $this->discountContributionPartner = formatTaka($this->ownDiscountContributionPartner + $this->serviceDiscountContributionPartner + $this->otherDiscountContributionPartner);

        /**
         * CALCULATING THE PRICES WITHOUT PARTNER CONTRIBUTION
         */
        $partner_contribution_without_service_discount_contribution = $this->discountContributionPartner - $this->getServiceDiscountContributionPartner() - $this->deliveryDiscountPartnerContribution;
        $this->servicePriceWithoutPartnerContribution = $this->getServicePriceWithoutPartnerContribution($partner_contribution_without_service_discount_contribution);
        $this->materialPriceWithoutPartnerContribution = $this->getMaterialPriceWithoutPartnerContribution($partner_contribution_without_service_discount_contribution);
        $this->deliveryPriceWithoutPartnerContribution = $this->getDeliveryPriceWithoutPartnerContribution($partner_contribution_without_service_discount_contribution);

        $this->serviceCost = $is_old_order
            ? formatTaka($this->servicePrice * $this->serviceCostRate)
            : formatTaka($this->servicePriceWithoutPartnerContribution * $this->serviceCostRate);
        $this->materialCost = $is_old_order
            ? formatTaka($this->materialPrice * $this->materialCostRate)
            : formatTaka($this->materialPriceWithoutPartnerContribution * $this->materialCostRate);
        $this->deliveryCost = $is_old_order
            ? formatTaka($this->delivery_charge * $this->deliveryCostRate)
            : formatTaka($this->deliveryPriceWithoutPartnerContribution * $this->deliveryCostRate);
        $this->logisticCostForPartner = formatTaka($this->logistic_charge * $this->logisticCostRateForPartner);
        $this->totalCostWithoutDiscount = formatTaka($this->serviceCost + $this->materialCost + $this->deliveryCost - $this->logisticCostForPartner);
        $this->commission = $this->totalPrice - $this->totalCostWithoutDiscount;

        /**
         * CALCULATING PROFIT
         */
        $this->totalCost = $is_old_order
            ? $this->totalCostWithoutDiscount - $this->discountContributionPartner
            : $this->totalCostWithoutDiscount;
        $this->grossCost = formatTaka($this->totalCost);
        $this->profit = formatTaka($this->grossPrice - $this->totalCost);
        $this->margin = $this->totalPrice != 0 ? (($this->grossPrice - $this->totalCost) * 100) / $this->totalPrice : 0;
        $this->margin = formatTaka($this->margin);

        if (isset($this->otherDiscountsByType[DiscountTypes::DELIVERY]))
            $this->deliveryDiscount = $this->otherDiscountsByType[DiscountTypes::DELIVERY];

        $this->discountWithoutDeliveryDiscount = ramp($this->discount - $this->deliveryDiscount);

        $this->grossLogisticCharge = ramp($this->logistic_charge - $this->logistic_discount);
        $this->logisticDueWithoutDiscount = $this->logistic_charge - $this->logistic_paid;
        $this->logisticDue = ramp($this->grossLogisticCharge - $this->logistic_paid);

        if (!$price_only) {
            $this->calculateComplexityIndex();
        }
        $this->isInWarranty = $this->isInWarranty();
        $this->isCalculated = true;
        return $this;
    }

    private function getServicePrice()
    {
        $total_service_price = 0;
        $total_service_surcharge = 0;
        foreach ($this->jobServices as $jobService) {
            $surcharge_amount = $jobService->surcharge_percentage ? ($jobService->unit_price * $jobService->surcharge_percentage) / 100 : 0;
            $unit_price_with_surcharge = $jobService->unit_price + $surcharge_amount;
            $total_service_price += ($jobService->min_price > ($unit_price_with_surcharge * $jobService->quantity) ?
                $jobService->min_price : ($unit_price_with_surcharge * $jobService->quantity));

            $total_service_surcharge += ($surcharge_amount * $jobService->quantity);
        }
        $this->totalServiceSurcharge = $total_service_surcharge;
        return $total_service_price;
    }

    private function getServiceDiscount()
    {
        $total_discount_price = 0;
        foreach ($this->jobServices as $jobService) {
            $total_discount_price += $jobService->discount;
        }
        return $total_discount_price;
    }

    private function getServiceDiscountContributionSheba()
    {
        $total_sheba_discount_contribution_price = 0;
        foreach ($this->jobServices as $jobService) {
            $total_sheba_discount_contribution_price += $jobService->discount * $jobService->sheba_contribution;
        }
        return $total_sheba_discount_contribution_price / 100;
    }

    private function getServiceDiscountContributionPartner()
    {
        $total_partner_discount_contribution_price = 0;
        foreach ($this->jobServices as $jobService) {
            $total_partner_discount_contribution_price += $jobService->discount * $jobService->partner_contribution;
        }
        return $total_partner_discount_contribution_price / 100;
    }

    private function calculateOtherDiscounts()
    {
        $this->otherDiscounts = $this->otherDiscountContributionSheba = $this->otherDiscountContributionPartner = 0;
        $this->otherDiscountsByType = $this->otherDiscountContributionShebaByType = $this->otherDiscountContributionPartnerByType = [];
        foreach ($this->discounts as $discount) {
            $this->otherDiscounts += $discount->amount;
            $sheba_contribution_amount = ($discount->amount * $discount->sheba_contribution) / 100;
            $partner_contribution_amount = ($discount->amount * $discount->partner_contribution) / 100;
            $this->otherDiscountContributionSheba += $sheba_contribution_amount;
            $this->otherDiscountContributionPartner += $partner_contribution_amount;
            if (!array_key_exists($discount->type, $this->otherDiscountsByType)) {
                $this->otherDiscountsByType[$discount->type] = 0;
                $this->otherDiscountContributionShebaByType[$discount->type] = 0;
                $this->otherDiscountContributionPartnerByType[$discount->type] = 0;
            }
            $this->otherDiscountsByType[$discount->type] += $discount->amount;
            $this->otherDiscountContributionShebaByType[$discount->type] += $sheba_contribution_amount;
            $this->otherDiscountContributionPartnerByType[$discount->type] += $partner_contribution_amount;
        }
        return $this;
    }

    public function isInWarranty()
    {
        if (!$this->isServed() || !$this->delivered_date) return false;
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
        return $this->codeBuilder->job($this);
    }

    public function fullCode()
    {
        return $this->codeBuilder->jobFull($this);
    }

    public function department()
    {
        return $this->partnerOrder->order->department();
    }

    public function getClosingTime()
    {
        if ($this->isServed()) {
            return $this->delivered_date;
        } elseif ($this->isCancelled()) {
            return $this->partnerChangeLog ? $this->partnerChangeLog->created_at : ($this->cancelLog ? $this->cancelLog->created_at : $this->updated_at);
        } else {
            return Carbon::now();
        }
    }

    /**
     * @return mixed
     */
    public function lifetime()
    {
        return $this->created_at->diffInDays($this->getClosingTime());
    }

    /**
     * @return boolean
     */
    public function canReschedule()
    {
        return constants('JOB_STATUS_SEQUENCE_FOR_ACTION')[$this->status] < constants('JOB_STATUS_SEQUENCE_FOR_ACTION')[JobStatuses::PROCESS];
    }

    /**
     * @return boolean
     */
    public function canCancel()
    {
        return constants('JOB_STATUS_SEQUENCE_FOR_ACTION')[$this->status] < constants('JOB_STATUS_SEQUENCE_FOR_ACTION')[JobStatuses::PROCESS];
    }

    /**
     * @return boolean
     */
    public function isScheduleDue()
    {
        return JobStatuses::isScheduleDue($this->status);
    }

    public function scopeInfo($query)
    {
        return $query->select(
            'jobs.id', 'jobs.discount', 'jobs.created_at', 'jobs.category_id', 'sheba_contribution', 'jobs.preferred_time_start',
            'partner_contribution', 'commission_rate', 'resource_id', 'schedule_date', 'service_variables',
            'job_additional_info', 'delivered_date', 'preferred_time', 'service_name',
            'status', 'site', 'service_quantity', 'service_unit_price', 'service_id', 'partner_order_id', 'jobs.delivery_charge',
            'first_logistic_order_id', 'last_logistic_order_id'
        );
    }

    public function scopeInfoV2($query)
    {
        return $query->select(
            'jobs.id', 'jobs.created_at', 'resource_id', 'schedule_date', 'preferred_time', 'status',
            'partner_order_id', 'resource_id', 'jobs.delivery_charge'
        );
    }

    /**
     * Scope a query to only include jobs for a given crm.
     *
     * @param Builder $query
     * @param $cm
     * @return Builder
     */
    public function scopeForCM($query, $cm)
    {
        if (is_array($cm)) {
            return $query->whereIn('crm_id', $cm);
        }
        if (!$cm) return $query->notAssigned();
        return $query->where('crm_id', $cm);
    }

    /**
     * @param Builder $query
     * @return mixed
     */
    public function scopeNotAssigned($query)
    {
        return $query->whereNull('crm_id');
    }

    /**
     * Scope a query to only include jobs for a given crm.
     *
     * @param Builder $query
     * @param $category_id
     * @return Builder
     */
    public function scopeForCategory($query, $category_id)
    {
        if (is_array($category_id)) {
            return $query->whereIn('category_id', $category_id);
        }

        return $query->where('category_id', $category_id);
    }

    /**
     * Scope a query to only include jobs of a given status.
     *
     * @param Builder $query
     * @param $status
     * @return Builder
     */
    public function scopeStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }
        return $query->where('status', $status);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeValidStatus($query)
    {
        return $query->status([JobStatuses::ACCEPTED, JobStatuses::SERVED,
            JobStatuses::PROCESS, JobStatuses::SCHEDULE_DUE, JobStatuses::SERVE_DUE]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeServed($query)
    {
        return $query->status(JobStatuses::SERVED);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeResource($query, $resource_id)
    {
        return $query->where('resource_id', $resource_id);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeTillNow($query)
    {
        return $query->where('schedule_date', '<=', date('Y-m-d'));
    }

    /**
     * @param Builder $query
     * @param Carbon $date
     */
    public function scopeDeliveredAt($query, Carbon $date)
    {
        $query->whereDate('delivered_date', '=', $date->toDateString());
    }

    /**
     * @param Builder $query
     * @param $field
     * @param TimeFrame $time_frame
     */
    public function scopeDateBetween($query, $field, TimeFrame $time_frame)
    {
        $query->whereBetween($field, $time_frame->getArray());
    }

    /**
     * @param Builder $query
     * @param TimeFrame $time_frame
     */
    public function scopeCreatedAtBetween($query, TimeFrame $time_frame)
    {
        $query->dateBetween('created_at', $time_frame);
    }

    /**
     * @param Builder $query
     * @param TimeFrame $time_frame
     */
    public function scopeDeliveredAtBetween($query, TimeFrame $time_frame)
    {
        $query->dateBetween('delivered_date', $time_frame);
    }

    /**
     * Scope a query to only include jobs of a given partner.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $partner
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForPartner($query, $partner)
    {
        return $query->whereHas('partnerOrder', function (Builder $q) use ($partner) {
            if (is_array($partner)) {
                return $q->whereIn('partner_orders.partner_id', $partner);
            }
            return $q->where('partner_orders.partner_id', $partner);
        });
    }

    /**
     * Scope a query to only include jobs of a given preferred time.
     *
     * @param Builder $query
     * @param $preferred_time
     * @return Builder
     */
    public function scopePreferredTime($query, $preferred_time)
    {
        if (is_array($preferred_time)) {
            return $query->whereIn('preferred_time', $preferred_time);
        }
        return $query->where('preferred_time', $preferred_time);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeScheduledToday($query)
    {
        return $query
            ->join('partner_orders', 'jobs.partner_order_id', '=', 'partner_orders.id')
            ->where('jobs.schedule_date', Carbon::today()->toDateString())
            ->whereIn('jobs.status', [JobStatuses::NOT_RESPONDED, JobStatuses::SCHEDULE_DUE, JobStatuses::SERVE_DUE,
                JobStatuses::DECLINED, JobStatuses::PROCESS, JobStatuses::ACCEPTED, JobStatuses::PENDING])
            ->selectRaw('count(distinct(partner_orders.order_id)) as total');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeOngoing($query)
    {
        return $query->status(JobStatuses::getOngoing());
    }

    public function customerComplains()
    {
        return $this->complains()->whereHas('accessor', function ($query) {
            $query->where('model_name', Customer::class);
        });
    }

    public function hasStatus(array $status)
    {
        foreach ($status as $key => $value) {
            $status[$key] = constants('JOB_STATUSES')[$value];
        }
        return in_array($this->status, $status);
    }

    public function getVersion()
    {
        return $this->partner_order_id > config('sheba.last_partner_order_id_v1') ? 'v2' : 'v1';
    }

    public function getReadablePreferredTimeAttribute()
    {
        if ($this->preferred_time !== 'Anytime') {
            return (new PreferredTime($this->preferred_time))->toReadableString();
        }
        return $this->preferred_time;
    }

    public function rescheduleCounter()
    {
        return $this->updateLogs->filter(function (JobUpdateLog $log) {
            return $log->isScheduleChangeLog();
        })->count();
    }

    public function priceChangeCounter()
    {
        return $this->updateLogs->filter(function ($update_log) {
            $decoded_log = $update_log->decoded_log;

            return str_contains($update_log->log, 'Service Price Updated') &&
                ($decoded_log['old_service_unit_price'] != $decoded_log['new_service_unit_price']);
        })->count();
    }

    public function isRentCar()
    {
        return in_array($this->category_id, array_map('intval', config('sheba.car_rental.secondary_category_ids'))) ? 1 : 0;
    }

    public function isAcceptable()
    {
        return JobStatuses::isAcceptable($this->status);
    }

    public function isProcessable()
    {
        return $this->_isProcessable() && !$this->needsLogistic();
    }

    private function _isProcessable()
    {
        return JobStatuses::isProcessable($this->status) && empty($this->first_logistic_order_id);
    }

    public function isServeable()
    {
        return JobStatuses::isServeable($this->status) && empty($this->first_logistic_order_id);
    }

    public function isReadyToPickable()
    {
        return ($this->isTwoWayReadyToPickable() || $this->isOneWayReadyToPickable()) && $this->needsLogistic();
    }

    private function isOneWayReadyToPickable()
    {
        return $this->category->needsOneWayLogisticOnReadyToPick() && JobStatuses::isProcessable($this->status) && !$this->first_logistic_order_id;
    }

    private function isTwoWayReadyToPickable()
    {
        return JobStatuses::isServeable($this->status) && $this->category->needsTwoWayLogistic() && empty($this->last_logistic_order_id);
    }

    public function canBeScheduleDue()
    {
        return JobStatuses::canBeScheduleDue($this->status);
    }

    public function canBeServeDue()
    {
        return JobStatuses::canBeServeDue($this->status);
    }

    public function canBeNotResponded()
    {
        return JobStatuses::canBeNotResponded($this->status);
    }

    public function isServed()
    {
        return $this->status == JobStatuses::SERVED;
    }

    public function isNotServed()
    {
        return !$this->isServed();
    }

    public function isClosed()
    {
        return in_array($this->status, JobStatuses::getClosed());
    }

    public function isCancelled()
    {
        return $this->status == JobStatuses::CANCELLED;
    }

    public function isCancelRequestPending()
    {
        /** @var JobCancelRequest $cancel_request */
        if ($cancel_request = $this->cancelRequest->last()) return $cancel_request->isPending();
        return false;
    }

    public function getPendingCancelRequest()
    {
        return $this->cancelRequest()->pending()->first();
    }

    public function isRentACarOrder()
    {
        return in_array($this->category_id, Category::getRentACarSecondaries());
    }

    public function isRentACarDateRangeOrder()
    {
        return $this->jobServices->first()->isRentACarDateRangeService();
    }

    public function getRentACarDateCount()
    {
        return $this->jobServices->first()->getRentACarDateCount();
    }

    public function canCallExpert()
    {
        if (!JobStatuses::isOngoing($this->status)) return false;

        return Carbon::today()->gte(Carbon::parse($this->schedule_date));
    }

    public function isOnPremise()
    {
        return $this->site == constants('JOB_ON_PREMISE')['partner'] ? 1 : 0;
    }

    public function masterCategory()
    {
        return $this->category->parent;
    }

    public function serviceIds()
    {
        return $this->services()->pluck('service_id')->implode(',');
    }

    public function lastCancelRequestBy()
    {
        $req = $this->cancelRequest()->orderBy('created_at', 'desc')->first();
        if ($req) {
            return $req->created_by_name;
        } else {
            return 'N/A';
        }
    }

    public function isNewOrderStructure()
    {
        return $this->partnerOrder->isNewOrderStructure();
    }

    /**
     * @return CategoryPartner
     */
    public function getPartnerCategory()
    {
        return CategoryPartner::where([
            'partner_id' => $this->partnerOrder->partner_id,
            'category_id' => $this->category_id,
        ])->first();
    }


    public function needsLogistic()
    {
        return (bool)$this->needs_logistic;
    }

    /**
     * @return bool
     */
    public function needsTwoWayLogistic()
    {
        return $this->needsLogistic() && $this->logistic_nature == LogisticNatures::TWO_WAY;
    }

    /**
     * @return bool
     */
    public function needsOneWayLogistic()
    {
        return $this->needsLogistic() && $this->logistic_nature == LogisticNatures::ONE_WAY;
    }

    /**
     * @return bool
     */
    public function needsOneWayLogisticOnAccept()
    {
        return $this->needsOneWayLogistic() && $this->one_way_logistic_init_event == OneWayLogisticInitEvents::ORDER_ACCEPT;
    }

    /**
     * @return bool
     */
    public function needsLogisticOnAccept()
    {
        return $this->needsTwoWayLogistic() || $this->needsOneWayLogisticOnAccept();
    }

    /**
     * @return bool
     */
    public function needsLogisticOnReadyToPick()
    {
        return $this->needsTwoWayLogistic() || $this->isOneWayReadyToPick();
    }

    public function isOneWayReadyToPick()
    {
        return $this->needsOneWayLogistic() && $this->one_way_logistic_init_event == OneWayLogisticInitEvents::READY_TO_PICK;
    }

    public function isLastLogisticOrderForTwoWay($id)
    {
        return $this->isTwoWay() && $this->last_logistic_order_id == $id;
    }

    /**
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function isLastLogisticOrder($id)
    {
        return $this->isOneWay() || $this->isLastLogisticOrderForTwoWay($id);
    }

    public function isOneWay()
    {
        return $this->needsOneWayLogistic();
    }

    public function isTwoWay()
    {
        return $this->needsTwoWayLogistic();
    }

    public function getShebaLogisticsPrice()
    {
        $parcel_repo = app(ParcelRepository::class);
        $parcel_details = $parcel_repo->findBySlug($this->logistic_parcel_type);

        if (!isset($parcel_details['price'])) return 0;

        return $this->needsTwoWayLogistic() ? $parcel_details['price'] * 2 : $parcel_details['price'];
    }

    public function isPayable()
    {
        return !JobStatuses::isServeable($this->status) && empty($this->first_logistic_order_id);
    }

    public function isOnlinePaymentDiscountApplicable()
    {
        $discount_threshold_minutes = config('sheba.online_payment_discount_threshold_minutes');
        return $discount_threshold_minutes ? $this->created_at->copy()->addMinutes($discount_threshold_minutes) >= Carbon::now() && $this->online_discount == 0 : 1;
    }

    public function isCapApplied()
    {
        return $this->discount_percentage && ($this->discount_percentage != $this->original_discount_amount);
    }

    public function isFlatPercentageDiscount()
    {
        return $this->discount_percentage && !$this->isCapApplied();
    }

    public function isOverDiscounted()
    {
        if ($this->isFlatPercentageDiscount()) return false;

        return $this->originalDiscount > $this->totalPrice;
    }

    public function getExtraDiscount()
    {
        return $this->isOverDiscounted() ? $this->originalDiscount - $this->totalPrice : 0;
    }

    public function isLogisticCreated()
    {
        return $this->first_logistic_order_id || $this->last_logistic_order_id;
    }

    public function getCurrentLogisticOrderId()
    {
        return $this->last_logistic_order_id ?: $this->first_logistic_order_id;
    }

    /**
     * @return \Sheba\Logistics\DTO\Order|null
     * @throws Exception
     */
    public function getCurrentLogisticOrder()
    {
        if (!$this->isLogisticCreated()) return null;
        if ($this->currentLogisticOrder) return $this->currentLogisticOrder;
        $this->currentLogisticOrder = $this->logisticOrderManager->getMinimal($this->getCurrentLogisticOrderId());

        return $this->currentLogisticOrder;
    }

    /**
     * @return \Sheba\Logistics\DTO\Order|null
     * @throws Exception
     */
    public function getFirstLogisticOrder()
    {
        if (!$this->first_logistic_order_id) return null;

        if ($this->firstLogisticOrder) return $this->firstLogisticOrder;

        $this->firstLogisticOrder = $this->logisticOrderManager->get($this->first_logistic_order_id);
        return $this->firstLogisticOrder;
    }

    /**
     * @return \Sheba\Logistics\DTO\Order|null
     * @throws Exception
     */
    public function getLastLogisticOrder()
    {
        if (!$this->last_logistic_order_id) return null;

        if ($this->lastLogisticOrder) return $this->lastLogisticOrder;

        $this->lastLogisticOrder = $this->logisticOrderManager->get($this->last_logistic_order_id);
        return $this->lastLogisticOrder;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isReschedulable()
    {
        if ($this->isClosed()) return false;
        if ($this->isCancelRequestPending()) return false;
        if ($this->hasLogisticStarted()) return false;
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isPartnerChangeable()
    {
        if ($this->site == 'partner') return false;
        if ($this->isClosed()) return false;
        if ($this->isCancelRequestPending()) return false;
        if ($this->hasLogisticStarted()) return false;
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isResourceChangeable()
    {
        if ($this->isClosed()) return false;
        if ($this->isCancelRequestPending()) return false;
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function hasLogisticStarted()
    {
        return $this->last_logistic_order_id ||
            ($this->first_logistic_order_id && !$this->getFirstLogisticOrder()->hasStarted());
    }

    public function isOnCustomerPremise()
    {
        return $this->site == Premises::CUSTOMER;
    }

    public function isOnPartnerPremise()
    {
        return $this->site == Premises::PARTNER;
    }

    public function toJson($options = 0)
    {
        $unnecessary = [
            "dates", "connection", "table", "primaryKey", "keyType", "perPage", "incrementing", "timestamps", "jobStatuses",
            "attributes", "original", "relations", "hidden", "visible", "appends", "fillable", "dateFormat", "guarded",
            "casts", "touches", "observables", "with", "morphClass", "exists", "wasRecentlyCreated", "materialPivotColumns",
        ];

        $array = get_object_vars($this) + $this->toArray();
        $array = array_except($array, $unnecessary);
        return json_encode($array, $options);
    }

    /**
     *
     * FUNCTIONS
     *
     */
    public function isRated()
    {
        return $this->review;
    }

    public function isNotRated()
    {
        return !$this->review;
    }

    public function hasResource()
    {
        return (int)$this->resource_id;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationHandlerClass()
    {
        return JobNotificationHandler::class;
    }

    public function hasPendingCancelRequest()
    {
        return $this->cancelRequests()->where('status', CancelRequestStatuses::PENDING)->count() > 0;
    }

    /**
     * @param $partner_contribution_without_service_discount_contribution
     * @return float|int
     */
    private function getServicePriceWithoutPartnerContribution($partner_contribution_without_service_discount_contribution)
    {
        if (!(int)$this->totalPriceWithoutVat) return 0.00;

        return $this->servicePrice
            - (($this->servicePrice / $this->totalPriceWithoutVat) * $partner_contribution_without_service_discount_contribution)
            - $this->getServiceDiscountContributionPartner();
    }

    /**
     * @param $partner_contribution_without_service_discount_contribution
     * @return float|int
     */
    private function getMaterialPriceWithoutPartnerContribution($partner_contribution_without_service_discount_contribution)
    {
        if (!(int)$this->totalPriceWithoutVat) return 0.00;

        return $this->materialPrice - (($this->materialPrice / $this->totalPriceWithoutVat) * $partner_contribution_without_service_discount_contribution);
    }

    /**
     * @param $partner_contribution_without_service_discount_contribution
     * @return float|int
     */
    private function getDeliveryPriceWithoutPartnerContribution($partner_contribution_without_service_discount_contribution)
    {
        if (!(int)$this->totalPriceWithoutVat) return 0.00;

        return $this->delivery_charge
            - (($this->delivery_charge / $this->totalPriceWithoutVat) * $partner_contribution_without_service_discount_contribution)
            - $this->deliveryDiscountPartnerContribution;
    }
}
