<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Sheba\Dal\BaseModel;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\PartnerOrderPayment\PartnerOrderPayment;
use Sheba\Dal\PartnerOrder\Events\PartnerOrderSaved;
use Sheba\Helpers\TimeFrame;
use Sheba\Order\Code\Builder as CodeBuilder;
use Sheba\PartnerOrder\PartnerOrderPaymentStatuses;
use Sheba\PartnerOrder\PartnerOrderStatuses;
use Sheba\PartnerOrder\StatusCalculator;
use Sheba\Payment\PayableType;
use Sheba\Report\Updater\PartnerOrder as ReportUpdater;
use Sheba\Report\Updater\UpdatesReport;
use  Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;

class PartnerOrder extends BaseModel implements PayableType, UpdatesReport
{
    use ReportUpdater;

    protected $guarded = ['id'];
    protected $dates = ['closed_at', 'closed_and_paid_at', 'cancelled_at'];
    protected $casts = ['partner_searched_count'];

    public $status;
    public $paymentStatus;
    public $paymentStatusWithLogistic;
    public $totalServicePrice;
    public $totalServiceCost;
    public $totalMaterialPrice;
    public $totalMaterialCost;
    public $totalPrice;
    public $gmv;
    public $serviceCharge;
    public $totalCommission;
    public $totalCost;
    public $grossAmount;
    public $roundingCutOff;
    public $paid;
    public $totalCollectedBySheba;
    public $totalCollectedBySP;
    public $due;
    public $overPaid;
    public $profit;
    public $revenue;
    public $profitBeforeDiscount;
    public $margin;
    public $marginBeforeDiscount;
    public $marginAfterDiscount;
    public $spPayable;
    public $shebaReceivable;
    public $totalDiscount;
    public $totalDiscountWithRoundingCutOff;
    public $jobDiscounts;
    public $jobPrices;
    public $financeDue;
    public $totalDiscountedCost;
    public $totalPartnerDiscount;
    public $totalShebaDiscount;
    public $totalCostWithoutDiscount;
    public $deliveryCharge;
    public $deliveryCost;
    public $isCalculated = false;
    public $revenuePercent = 0;
    public $serviceChargePercent = 0;
    public $totalLogisticCharge = 0;
    public $grossLogisticCharge = 0;
    public $totalLogisticPaid = 0;
    public $totalLogisticDue = 0;
    public $paidWithLogistic = 0;
    public $dueWithLogistic = 0;
    public $totalLogisticDueWithoutDiscount;
    public $jobPricesWithLogistic;
    public $grossAmountWithLogistic;
    public $totalDeliveryDiscount = 0.00;
    public $totalDeliveryDiscountShebaContribution = 0.00;
    public $totalDeliveryDiscountPartnerContribution = 0.00;

    /** @var CodeBuilder */
    private $codeBuilder;
    private $statusCalculator;

    public static $savedEventClass = PartnerOrderSaved::class;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->codeBuilder = new CodeBuilder();
        $this->statusCalculator = new StatusCalculator();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function payments()
    {
        return $this->hasMany(PartnerOrderPayment::class);
    }

    public function transactions()
    {
        return $this->hasMany(PartnerTransaction::class);
    }

    public function partnerOrderRequests()
    {
        return $this->hasMany(PartnerOrderRequest::class);
    }

    public function code()
    {
        return $this->order->code() . "-" . str_pad($this->partner_id, 4, '0', STR_PAD_LEFT);
    }

    public function calculate($price_only = false)
    {
        $this->_calculateThisJobs($price_only);
        $this->calculateStatus();
        $this->totalDiscount = $this->jobDiscounts + $this->discount;
        $this->_calculateRoundingCutOff();
        $this->gmv = floatValFormat($this->jobPrices);
        $this->serviceCharge = floatValFormat($this->gmv - ($this->totalCostWithoutDiscount + $this->totalPartnerDiscount));
        $this->serviceChargePercent = floatValFormat($this->gmv > 0 ? ($this->serviceCharge * 100) / $this->gmv : 0);
        $this->grossAmount = floatValFormat($this->totalPrice - $this->discount - $this->roundingCutOff);
        $this->jobPricesWithLogistic = $this->jobPrices + $this->totalLogisticCharge;
        $this->grossAmountWithLogistic = $this->grossAmount + $this->grossLogisticCharge;
        $this->paid = $this->sheba_collection + $this->partner_collection;
        $this->due = round(floatValFormat($this->grossAmount - $this->paid));
        $this->paidWithLogistic = floatValFormat($this->paid + $this->totalLogisticPaid);
        $this->dueWithLogistic = round(floatValFormat($this->due + $this->totalLogisticDue));
        $this->overPaid = $this->isOverPaid() ? floatValFormat($this->paid - $this->grossAmount) : 0;
        $this->profitBeforeDiscount = floatValFormat($this->jobPrices - $this->totalCost);
        $this->totalDiscountedCost = ($this->totalDiscountedCost < 0) ? 0 : $this->totalDiscountedCost;
        $this->totalDiscountWithRoundingCutOff = $this->totalDiscount + $this->roundingCutOff;
        $this->profit = floatValFormat($this->grossAmount - $this->totalCost);
        $this->revenue = floatValFormat($this->gmv - ($this->totalCostWithoutDiscount + $this->totalPartnerDiscount + $this->totalShebaDiscount));
        $this->revenuePercent = floatValFormat($this->gmv > 0 ? $this->revenue * 100 / $this->gmv : 0);
        $this->margin = $this->totalPrice ? (floatValFormat($this->totalPrice - $this->totalCost) * 100) / $this->totalPrice : 0;
        $this->marginBeforeDiscount = $this->jobPrices ? (floatValFormat($this->jobPrices - $this->totalCost) * 100) / $this->jobPrices : 0;
        $this->marginAfterDiscount = $this->grossAmount ? (floatValFormat($this->grossAmount - $this->totalCost) * 100) / $this->grossAmount : 0;
        $this->spPayable = ($this->partner_collection < $this->totalCost) ? (floatValFormat($this->totalCost - $this->partner_collection)) : 0;
        $this->shebaReceivable = ($this->sheba_collection < $this->profit) ? (floatValFormat($this->profit - $this->sheba_collection)) : 0;
        $this->_setPaymentStatus()->_setFinanceDue();
        $this->isCalculated = true;
        return $this->_formatAllToTaka();
    }

    public function isOverPaid()
    {
        return $this->paid > $this->grossAmount;
    }

    public function getTotalShebaCollectionAttribute()
    {
        return $this->getTotalCollection('Sheba');
    }

    public function getTotalSpCollectionAttribute()
    {
        return $this->getTotalCollection('Partner');
    }

    private function getTotalCollection($of = null)
    {
        $payments = $this->payments;
        if (!empty($payments)) $payments = $payments->where('collected_by', $of);
        return $payments->sum('amount');
    }

    private function _setPaymentStatus()
    {
        $this->paymentStatus = ($this->due) ? "Due" : "Paid";
        return $this;
    }

    private function _setFinanceDue()
    {
        if ($this->due) {
            $this->financeDue = true;
        } else {
            if ($this->finance_collection == $this->paid) {
                $this->financeDue = false;
            } else if ($this->finance_collection == $this->profit && $this->partner_collection == $this->totalCost) {
                $this->financeDue = false;
            } else {
                $this->financeDue = true;
            }
        }
        return $this;
    }

    private function _calculateThisJobs($price_only = false)
    {
        //$this->_initializeStatusCounter();
        $this->_initializeTotalsToZero();
        foreach ($this->jobs as $job) {
            /** @var Job $job */
            $job = $job->calculate($price_only);
            if (!$job->isCancelled()) {
                $this->_updateTotalPriceAndCost($job);
            }
            //$this->jobStatusCounter[$job->status]++;
            //$this->totalJobs++;
        }
        //$this->_setStatus();
        return $this;
    }

    /**
     * @param Job $job
     */
    private function _updateTotalPriceAndCost(Job $job)
    {
        $this->totalServicePrice += $job->servicePrice;
        $this->totalServiceCost += $job->serviceCost;
        $this->totalMaterialPrice += $job->materialPrice;
        $this->totalMaterialCost += $job->materialCost;
        $this->jobPrices += $job->totalPrice;
        $this->totalPrice += $job->grossPrice;
        $this->totalCostWithoutDiscount += $job->totalCostWithoutDiscount;
        $this->totalCost += $job->totalCost;
        $this->totalCommission += $job->commission;
        $this->totalDiscountedCost += $job->totalCost;
        $this->jobDiscounts += $job->discount;
        $this->totalPartnerDiscount += $job->discountContributionPartner;
        $this->totalShebaDiscount += $job->discountContributionSheba;
        $this->deliveryCharge += $job->delivery_charge;
        $this->deliveryCost += $job->deliveryCost;
        $this->totalLogisticCharge += $job->logistic_charge;
        $this->grossLogisticCharge += $job->grossLogisticCharge;
        $this->totalLogisticPaid += $job->logistic_paid;
        $this->totalLogisticDue += $job->logisticDue;
        $this->totalLogisticDueWithoutDiscount += $job->logisticDueWithoutDiscount;
        $this->totalDeliveryDiscount += $job->deliveryDiscount;
        $this->totalDeliveryDiscountShebaContribution += isset($job->otherDiscountContributionShebaByType[DiscountTypes::DELIVERY]) ? $job->otherDiscountContributionShebaByType[DiscountTypes::DELIVERY] : 0.00;
        $this->totalDeliveryDiscountPartnerContribution += isset($job->otherDiscountContributionPartnerByType[DiscountTypes::DELIVERY]) ? $job->otherDiscountContributionPartnerByType[DiscountTypes::DELIVERY] : 0.00;
    }

    private function _calculateRoundingCutOff()
    {
        // for now:
        /*$this->roundingCutOff = 0;*/

        $total = $this->totalPrice - floatval($this->discount);
        $this->roundingCutOff = $total - floor($total);
        return $this;
    }

    private function _formatAllToTaka()
    {
        $this->totalServicePrice = formatTaka($this->totalServicePrice);
        $this->totalServiceCost = formatTaka($this->totalServiceCost);
        $this->totalMaterialPrice = formatTaka($this->totalMaterialPrice);
        $this->totalMaterialCost = formatTaka($this->totalMaterialCost);
        $this->totalPrice = formatTaka($this->totalPrice);
        $this->totalCost = formatTaka($this->totalCost);
        $this->roundingCutOff = formatTaka($this->roundingCutOff);
        $this->grossAmount = formatTaka($this->grossAmount);
        $this->paid = formatTaka($this->paid);
        $this->due = formatTaka($this->due);
        $this->profit = formatTaka($this->profit);
        $this->revenue = formatTaka($this->revenue);
        $this->profitBeforeDiscount = formatTaka($this->profitBeforeDiscount);
        $this->margin = formatTaka($this->margin);
        $this->marginBeforeDiscount = formatTaka($this->marginBeforeDiscount);
        $this->marginAfterDiscount = formatTaka($this->marginAfterDiscount);
        $this->spPayable = formatTaka($this->spPayable);
        $this->shebaReceivable = formatTaka($this->shebaReceivable);
        $this->jobPrices = formatTaka($this->jobPrices);
        $this->totalDiscount = formatTaka($this->totalDiscount);
        $this->totalDiscountedCost = formatTaka($this->totalDiscountedCost);
        $this->totalPartnerDiscount = formatTaka($this->totalPartnerDiscount);
        $this->totalShebaDiscount = formatTaka($this->totalShebaDiscount);
        $this->totalCostWithoutDiscount = formatTaka($this->totalCostWithoutDiscount);
        $this->serviceCharge = formatTaka($this->serviceCharge);
        $this->deliveryCharge = formatTaka($this->deliveryCharge);
        $this->deliveryCost = formatTaka($this->deliveryCost);
        $this->revenuePercent = formatTaka($this->revenuePercent);
        $this->serviceChargePercent = formatTaka($this->serviceChargePercent);
        return $this;
    }

    private function _initializeTotalsToZero()
    {
        $this->totalServicePrice = 0;
        $this->totalServiceCost = 0;
        $this->totalMaterialPrice = 0;
        $this->totalMaterialCost = 0;
        $this->jobPrices = 0;
        $this->totalPrice = 0;
        $this->totalCostWithoutDiscount = 0;
        $this->totalCost = 0;
        $this->totalCommission = 0;
        $this->totalDiscountedCost = 0;
        $this->jobDiscounts = 0;
        $this->totalPartnerDiscount = 0;
        $this->totalShebaDiscount = 0;
        $this->deliveryCharge = 0;
        $this->deliveryCost = 0;
        $this->totalLogisticCharge = 0;
        $this->totalLogisticPaid = 0;
        $this->totalLogisticDue = 0;
        $this->totalLogisticDueWithoutDiscount = 0;
    }

    public function calculateStatus()
    {
        /*$this->_initializeStatusCounter();
        foreach($this->jobs as $job) {
            $this->jobStatusCounter[$job->status]++;
            $this->totalJobs++;
        }
        $this->_setStatus();*/
        $this->status = $this->getStatus();
        return $this;
    }

    public function getStatus()
    {
        StatusCalculator::initialize();
        return StatusCalculator::calculate($this);
    }

    public function scopeClosedAt($query, Carbon $date)
    {
        $query->whereDate('closed_at', '=', $date->toDateString());
    }

    public function scopeDateBetween($query, $field, TimeFrame $time_frame)
    {
        $query->whereBetween($field, $time_frame->getArray());
    }

    public function scopeCreatedAtBetween($query, TimeFrame $time_frame)
    {
        $query->dateBetween('created_at', $time_frame);
    }

    public function scopeClosedAtBetween($query, TimeFrame $time_frame)
    {
        $query->dateBetween('closed_at', $time_frame);
    }

    public function scopeCancelledAtBetween($query, TimeFrame $time_frame)
    {
        $query->dateBetween('cancelled_at', $time_frame);
    }

    public function scopeOf($query, $partner)
    {
        if (is_array($partner)) $query->whereIn('partner_id', $partner);
        else $query->where('partner_id', '=', $partner);
    }

    public function scopeOngoing($query)
    {
        return $query->where([
            ['cancelled_at', null],
            ['closed_and_paid_at', null]
        ]);
    }

    public function scopeClosedButNotPaid($query)
    {
        return $query->where([
            ['closed_at', '<>', null],
            ['closed_and_paid_at', null]
        ]);
    }

    public function scopeNew($query)
    {
        $query->where('cancelled_at', '<>', null)->orWhere('closed_and_paid_at', '<>', null);
    }

    public function scopeNotBadDebt($q)
    {
        return $q->where('payment_method', '<>', 'bad-debt');
    }

    /**
     * @param Builder $query
     */
    public function scopeNotCompleted($query)
    {
        $query->whereNull('cancelled_at')->whereNull('closed_and_paid_at');
    }

    public function scopeHistory($query)
    {
        return $query->where([['closed_and_paid_at', '<>', null], ['cancelled_at', null]])->orWhere('cancelled_at', '<>', null);
    }

    public function scopeCancelled($query)
    {
        $query->where('cancelled_at', '<>', null);
    }

    public function scopeNotCancelled($query)
    {
        $query->where('cancelled_at', null);
    }

    public function stageChangeLogs()
    {
        return $this->hasMany(PartnerOrderStatusLog::class);
    }

    public function statusChangeLogs()
    {
        return $this->hasMany(PartnerOrderStatusLog::class);
    }

    public function getVersion()
    {
        return $this->id > (double)env('LAST_PARTNER_ORDER_ID_V1') ? 'v2' : 'v1';
    }

    public function getIsV2Attribute()
    {
        return $this->id > (double)env('LAST_PARTNER_ORDER_ID_V1');
    }

    public function isCancelled()
    {
        return $this->getStatus() == PartnerOrderStatuses::CANCELLED;
    }

    public function lifetime()
    {
        if ($this->cancelled_at) {
            $closed_date = $this->cancelled_at;
        } elseif ($this->closed_at) {
            $closed_date = $this->closed_at;
        } else {
            $closed_date = Carbon::now();
        }

        return $this->created_at->diffInHours($closed_date);
    }

    public function getAllCm()
    {
        $cm = collect([]);
        $this->jobs->each(function ($job) use ($cm) {
            $cm->push($job->crm);
        });
        return $cm;
    }

    /**
     * @return Collection
     */
    public function getAllCmNames()
    {
        return $this->getAllCm()->map(function ($cm) {
            return $cm ? $cm->name : "N/A";
        });
    }

    /**
     * @return Job
     */
    public function getActiveJob()
    {
        return $this->jobs()->where('status', '<>', constants('JOB_STATUSES')['Cancelled'])->first();
    }

    public function lastJob()
    {
        if ($this->isCancelled()) return $this->jobs->last();
        return $this->jobs->filter(function (Job $job) {
            return !$job->isCancelled();
        })->first();
    }

    public function getActiveJobAttribute()
    {
        return $this->jobs->first();
    }

    public function isDue()
    {
        return $this->paymentStatus == PartnerOrderPaymentStatuses::DUE;
    }

    public function isPaid()
    {
        return $this->paymentStatus == PartnerOrderPaymentStatuses::PAID;
    }

    public function isDueWithLogistic()
    {
        return $this->dueWithLogistic != 0.00;
    }

    public function isPaidWithLogistic()
    {
        return $this->dueWithLogistic == 0.00;
    }

    public function isClosedAndPaidAt()
    {
        return $this->closed_and_paid_at ? 1 : 0;
    }

    public function isClosed()
    {
        return $this->closed_at ? 1 : 0;
    }

    public function isAlreadyCancelled()
    {
        return $this->cancelled_at ? 1 : 0;
    }

    public function getCustomerPayable()
    {
        return (double)$this->dueWithLogistic;
    }

    public function scopeNotB2bOrder($query)
    {
        return $query->whereHas('order', function ($q) {
            $q->where('sales_channel', '<>', "B2B");
        });
    }
}
