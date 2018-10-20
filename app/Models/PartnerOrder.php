<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\PartnerOrder\StatusCalculator;

class PartnerOrder extends Model
{
    private $jobStatuses;
    public $status;
    public $paymentStatus;
    public $totalServicePrice;
    public $totalServiceCost;
    public $totalMaterialPrice;
    public $totalMaterialCost;
    public $totalPrice;
    public $totalCost;
    public $grossAmount;
    public $roundingCutOff;
    public $paid;
    public $due;
    public $profit;
    public $profitBeforeDiscount;
    public $margin;
    public $marginBeforeDiscount;
    public $marginAfterDiscount;
    public $spPayable;
    public $shebaReceivable;
    public $totalDiscount;
    public $jobDiscounts;
    public $jobPrices;
    public $financeDue;
    public $totalDiscountedCost;
    public $totalPartnerDiscount;
    public $totalShebaDiscount;
    public $totalCostWithoutDiscount;
    public $deliveryCharge;
    public $isCalculated;

    protected $guarded = ['id'];
    protected $dates = ['closed_at', 'closed_and_paid_at'];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        StatusCalculator::initialize();
        $this->jobStatuses = StatusCalculator::$jobStatuses;
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
        $this->grossAmount = floatValFormat($this->totalPrice - $this->discount - $this->roundingCutOff);
        $this->paid = $this->sheba_collection + $this->partner_collection;
        $this->due = floatValFormat($this->grossAmount - $this->paid);
        $this->profitBeforeDiscount = floatValFormat($this->jobPrices - $this->totalCost);
        $this->totalDiscountedCost = ($this->totalDiscountedCost < 0) ? 0 : $this->totalDiscountedCost;
        $this->profit = floatValFormat($this->grossAmount - $this->totalCost);
        $this->margin = $this->totalPrice ? (floatValFormat($this->totalPrice - $this->totalCost) * 100) / $this->totalPrice : 0;
        $this->marginBeforeDiscount = $this->jobPrices ? (floatValFormat($this->jobPrices - $this->totalCost) * 100) / $this->jobPrices : 0;
        $this->marginAfterDiscount = $this->grossAmount ? (floatValFormat($this->grossAmount - $this->totalCost) * 100) / $this->grossAmount : 0;
        $this->spPayable = ($this->partner_collection < $this->totalCost) ? (floatValFormat($this->totalCost - $this->partner_collection)) : 0;
        $this->shebaReceivable = ($this->sheba_collection < $this->profit) ? (floatValFormat($this->profit - $this->sheba_collection)) : 0;
        $this->_setPaymentStatus()->_setFinanceDue();
        $this->isCalculated = true;
        return $this->_formatAllToTaka();
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
            $job = $job->calculate($price_only);
            if ($job->status != $this->jobStatuses['Cancelled']) {
                $this->_updateTotalPriceAndCost($job);
            }
            //$this->jobStatusCounter[$job->status]++;
            //$this->totalJobs++;
        }
        //$this->_setStatus();
        return $this;
    }

    /**
     * @param $job
     */
    private function _updateTotalPriceAndCost($job)
    {
        $this->totalServicePrice += $job->servicePrice;
        $this->totalServiceCost += $job->serviceCost;
        $this->totalMaterialPrice += $job->materialPrice;
        $this->totalMaterialCost += $job->materialCost;
        $this->jobPrices += $job->totalPrice;
        $this->totalPrice += $job->grossPrice;
        $this->totalCostWithoutDiscount += $job->totalCostWithoutDiscount;
        $this->totalCost += $job->totalCost;
        $this->totalDiscountedCost += $job->totalCost;
        $this->jobDiscounts += $job->discount;
        $this->totalPartnerDiscount += $job->discountContributionPartner;
        $this->totalShebaDiscount += $job->discountContributionSheba;
        $this->deliveryCharge += $job->delivery_charge;
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
        return $this;
    }

    private function _initializeTotalsToZero()
    {
        //$this->totalJobs = 0;
        $this->totalServicePrice = 0;
        $this->totalServiceCost = 0;
        $this->totalMaterialPrice = 0;
        $this->totalMaterialCost = 0;
        $this->totalPrice = 0;
        $this->totalCost = 0;
        $this->totalDiscountedCost = 0;
        $this->jobDiscounts = 0;
        $this->jobPrices = 0;
        $this->totalPartnerDiscount = 0;
        $this->totalShebaDiscount = 0;
        $this->totalCostWithoutDiscount = 0;
        $this->deliveryCharge = 0;
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

    public function scopeHistory($query)
    {
        return $query->where([['closed_and_paid_at', '<>', null], ['cancelled_at', null]]);
    }

    public function stageChangeLogs()
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
}
