<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerOrder extends Model
{
    protected $guarded = ['id'];

    public $status;
    public $paymentStatus;
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
    public $margin;
    public $marginAfterDiscount;
    public $spPayable;
    public $shebaReceivable;

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

    public function calculate()
    {
        $this->_calculateThisJobs();
        $this->_calculateRoundingCutOff();
        $this->grossAmount = $this->totalPrice - $this->discount  - $this->roundingCutOff;
        $this->paid = $this->sheba_collection + $this->partner_collection;
        $this->due = $this->grossAmount - $this->paid;
        $this->profit = $this->grossAmount - $this->totalCost;
        $this->margin = ( ($this->totalPrice - $this->totalCost) * 100 ) / $this->totalPrice;
        $this->marginAfterDiscount = ( ($this->grossAmount - $this->totalCost) * 100 ) / $this->grossAmount;
        $this->spPayable = ($this->partner_collection < $this->totalCost) ? ($this->totalCost - $this->partner_collection) : 0;
        $this->shebaReceivable = ($this->partner_collection > $this->totalCost) ? ($this->partner_collection - $this->totalCost) : 0;

        if($this->due)
            $this->paymentStatus = "Due";
        else
            $this->paymentStatus = "Paid";

        return $this->_formatAllToTaka();
    }

    private function _calculateThisJobs()
    {

        $job_statuses = constants('JOB_STATUSES');
        $po_statuses = constants('PARTNER_ORDER_STATUSES');

        $total_jobs = 0;
        $job_status_counter = [
            $job_statuses['Pending'] => 0,
            $job_statuses['Accepted'] => 0,
            $job_statuses['Declined'] => 0,
            $job_statuses['Not Responded'] => 0,
            $job_statuses['Schedule Due'] => 0,
            $job_statuses['Process'] => 0,
            $job_statuses['Served'] => 0,
            $job_statuses['Cancelled'] => 0
        ];

        $this->totalServiceCost = 0;
        $this->totalMaterialPrice = 0;
        $this->totalMaterialCost = 0;
        $this->totalPrice = 0;
        $this->totalCost = 0;
        foreach($this->jobs as $job) {
            $job = $job->calculate();

            $this->totalServiceCost += $job->serviceCost;
            $this->totalMaterialPrice += $job->materialPrice;
            $this->totalMaterialCost += $job->materialCost;
            $this->totalPrice += $job->grossPrice;
            $this->totalCost += $job->grossCost;

            $job_status_counter[$job->status]++;
            $total_jobs++;
        }

        if($job_status_counter[$job_statuses['Pending']] == $total_jobs) {
            $this->status = $po_statuses['Open'];
        } else if($job_status_counter[$job_statuses['Cancelled']] == $total_jobs) {
            $this->status = $po_statuses['Cancelled'];
        } else if($job_status_counter[$job_statuses['Served']] == $total_jobs) {
            $this->status = $po_statuses['Closed'];
        } else {
            $this->status = $po_statuses['Process'];
        }

        return $this;
    }

    private function _calculateRoundingCutOff()
    {
        $total = $this->totalPrice - $this->discount;
        $whole = floor($total);
        $fraction = $total - $whole;
        $this->roundingCutOff = ( $whole % 5 ) + $fraction;
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
        $this->margin = formatTaka($this->margin);
        $this->marginAfterDiscount = formatTaka($this->marginAfterDiscount);
        $this->spPayable = formatTaka($this->spPayable);
        $this->shebaReceivable = formatTaka($this->shebaReceivable);
        return $this;
    }

    public function code()
    {
        return $this->order->code() . "-" . str_pad($this->partner_id, 4,'0',STR_PAD_LEFT);
    }

    public function allJobsDelivered()
    {
        return false;
    }
}
