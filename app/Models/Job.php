<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $materialPivotColumns = ['id', 'material_name', 'material_price', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];
    protected $guarded = ['id'];

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

    public function service()
    {
        return $this->belongsTo(Service::class);
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

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function calculate()
    {
        //$this->commissionRate = $this->partnerOrder->partner->categories()->find($this->service->category->id)->pivot->commission;
        $costRate = 1 - ($this->commission_rate / 100);

        $this->servicePrice = formatTaka($this->service_unit_price * $this->service_quantity);
        $this->serviceCost = formatTaka($this->servicePrice * $costRate);
        $this->materialPrice = formatTaka($this->calculateMaterialPrice());
        $this->materialCost = formatTaka($this->materialPrice * $costRate);
        $this->totalCostWithoutDiscount = formatTaka($this->serviceCost + $this->materialCost);
        $this->totalPriceWithoutVat = formatTaka($this->servicePrice + $this->materialPrice);
        //$this->totalPrice = formatTaka($this->totalPriceWithoutVat + $this->vat); // later
        $this->totalPrice = formatTaka($this->totalPriceWithoutVat);
        $this->grossPrice = formatTaka($this->totalPrice - $this->discount);
        $this->service_unit_price = formatTaka($this->service_unit_price);
        $this->discountContributionSheba = formatTaka(($this->discount * $this->sheba_contribution) / 100);
        $this->discountContributionPartner = formatTaka(($this->discount * $this->partner_contribution) / 100);
        $this->totalCost = $this->totalCostWithoutDiscount - $this->discountContributionPartner;
        $this->grossCost = formatTaka($this->totalCost);
        $this->profit = formatTaka($this->grossPrice - $this->totalCost);
        $this->margin = ($this->totalPrice != 0) ? (($this->grossPrice - $this->totalCost) * 100) / $this->totalPrice : 0;
        $this->margin = formatTaka($this->margin);
        return $this;
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

    public function scopeInfo($query)
    {
        return $query->select('jobs.id', 'jobs.discount', 'jobs.created_at', 'resource_id', 'schedule_date', 'preferred_time', 'service_name', 'status', 'service_quantity', 'service_unit_price', 'service_id', 'partner_order_id');
    }

    public function scopeValidStatus($query)
    {
        return $query->whereIn('status', ['Accepted', 'Served', 'Process', 'Schedule Due']);
    }

    public function scopeTillNow($query)
    {
        return $query->where('schedule_date', '<=', date('Y-m-d'));
    }

    public function scopeStatus($query, Array $status)
    {
        return $query->whereIn('status', $status);
    }
}
