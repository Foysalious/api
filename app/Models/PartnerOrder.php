<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerOrder extends Model
{
    protected $guarded = ['id'];

    public $totalServiceCost;
    public $totalMaterialPrice;
    public $totalMaterialCost;
    public $totalPrice;
    public $totalCost;
    public $grossAmount;
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
        $this->grossAmount = $this->totalPrice - $this->discount  - $this->rounding_cut_off;
        $this->paid = $this->sheba_collection + $this->partner_collection;
        $this->due = $this->grossAmount - $this->paid;
        $this->profit = $this->grossAmount - $this->totalCost;
        $this->margin = ( ($this->totalPrice - $this->totalCost) * 100 ) / $this->totalPrice;
        $this->marginAfterDiscount = ( ($this->grossAmount - $this->totalCost) * 100 ) / $this->grossAmount;
        $this->spPayable = ($this->partner_collection < $this->totalCost) ? ($this->totalCost - $this->partner_collection) : 0;
        $this->shebaReceivable = ($this->partner_collection > $this->totalCost) ? ($this->partner_collection - $this->totalCost) : 0;
        return $this->_formatAllToTaka();
    }

    private function _calculateThisJobs()
    {
        $this->totalServiceCost = 0;
        $this->totalMaterialPrice = 0;
        $this->totalMaterialCost = 0;
        $this->totalPrice = 0;
        $this->totalCost = 0;
        foreach($this->jobs as $job) {
            $job = $job->calculate();

            $this->totalServiceCost += $job->service_cost;
            $this->totalMaterialPrice += $job->materialPrice;
            $this->totalMaterialCost += $job->materialCost;
            $this->totalPrice += $job->grossPrice;
            $this->totalCost += $job->grossCost;
        }
        return $this;
    }

    private function _formatAllToTaka()
    {
        $this->totalServiceCost = formatTaka($this->totalServiceCost);
        $this->totalMaterialPrice = formatTaka($this->totalMaterialPrice);
        $this->totalMaterialCost = formatTaka($this->totalMaterialCost);
        $this->totalPrice = formatTaka($this->totalPrice);
        $this->totalCost = formatTaka($this->totalCost);
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
        return str_pad($this->order_id, 6,'0',STR_PAD_LEFT) . "-" . str_pad($this->partner_id, 4,'0',STR_PAD_LEFT);
    }

    public function allJobsDelivered()
    {
        return false;
    }
}
