<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerOrder extends Model
{
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

    protected $guarded = ['id'];
    protected $dates = ['closed_at', 'closed_and_paid_at'];

    private $totalJobs;
    private $jobStatuses;
    private $statuses;
    private $jobStatusCounter;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->jobStatuses = constants('JOB_STATUSES');
        $this->statuses = constants('PARTNER_ORDER_STATUSES');
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

    public function code()
    {
        return $this->order->code() . "-" . str_pad($this->partner_id, 4, '0', STR_PAD_LEFT);
    }

    public function calculate($price_only = false)
    {
        $this->_calculateThisJobs($price_only);
        $this->totalDiscount = $this->jobDiscounts + $this->discount;
        $this->_calculateRoundingCutOff();
        $this->grossAmount = $this->totalPrice - $this->discount - $this->roundingCutOff;
        $this->paid = $this->sheba_collection + $this->partner_collection;
        $this->due = $this->grossAmount - $this->paid;
        $this->profitBeforeDiscount = $this->jobPrices - $this->totalCost;
        $this->totalDiscountedCost = ($this->totalDiscountedCost < 0) ? 0 : $this->totalDiscountedCost;
        $this->profit = $this->grossAmount - $this->totalCost;
        $this->margin = $this->totalPrice ? (($this->totalPrice - $this->totalCost) * 100) / $this->totalPrice : 0;
        $this->marginBeforeDiscount = $this->jobPrices ? (($this->jobPrices - $this->totalCost) * 100) / $this->jobPrices : 0;
        $this->marginAfterDiscount = $this->grossAmount ? (($this->grossAmount - $this->totalCost) * 100) / $this->grossAmount : 0;
        $this->spPayable = ($this->partner_collection < $this->totalCost) ? ($this->totalCost - $this->partner_collection) : 0;
        $this->shebaReceivable = ($this->sheba_collection < $this->profit) ? ($this->profit - $this->sheba_collection) : 0;
        $this->_setPaymentStatus()->_setFinanceDue();
        return $this->_formatAllToTaka();
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
        $this->_initializeStatusCounter();
        $this->_initializeTotalsToZero();
        foreach ($this->jobs as $job) {
            $job = $job->calculate($price_only);
            if ($job->status != $this->jobStatuses['Cancelled']) {
                $this->_updateTotalPriceAndCost($job);
            }
            $this->jobStatusCounter[$job->status]++;
            $this->totalJobs++;
        }
        $this->_setStatus();
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

    private function _initializeStatusCounter()
    {
        $this->jobStatusCounter = [
            $this->jobStatuses['Pending'] => 0,
            $this->jobStatuses['Accepted'] => 0,
            $this->jobStatuses['Declined'] => 0,
            $this->jobStatuses['Not_Responded'] => 0,
            $this->jobStatuses['Schedule_Due'] => 0,
            $this->jobStatuses['Process'] => 0,
            $this->jobStatuses['Served'] => 0,
            $this->jobStatuses['Cancelled'] => 0
        ];
    }

    private function _initializeTotalsToZero()
    {
        $this->totalJobs = 0;
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
    }

    private function _setStatus()
    {
        if ($this->isAllJobsCancelled()) {
            $this->status = $this->statuses['Cancelled'];
        } else if ($this->isAllJobsPending()) {
            $this->status = $this->statuses['Open'];
        } else if ($this->isAllJobsServed()) {
            $this->status = $this->statuses['Closed'];
        } else {
            $this->status = $this->statuses['Process'];
        }
    }

    /**
     * @return bool
     */
    private function isAllJobsCancelled()
    {
        return $this->jobStatusCounter[$this->jobStatuses['Cancelled']] == $this->totalJobs;
    }

    /**
     * @return bool
     */
    private function isAllJobsPending()
    {
        $pending_jobs = $this->jobStatusCounter[$this->jobStatuses['Pending']];
        $cancelled_jobs = $this->jobStatusCounter[$this->jobStatuses['Cancelled']];
        return $pending_jobs + $cancelled_jobs == $this->totalJobs;
    }

    /**
     * @return bool
     */
    private function isAllJobsServed()
    {
        $served_jobs = $this->jobStatusCounter[$this->jobStatuses['Served']];
        $cancelled_jobs = $this->jobStatusCounter[$this->jobStatuses['Cancelled']];
        return $served_jobs + $cancelled_jobs == $this->totalJobs;
    }
}
