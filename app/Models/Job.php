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
    public $grossPrice;

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

    public function complains()
    {
        return $this->hasMany(Complain::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
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
        $this->materialPrice = formatTaka($this->usedMaterials()->sum('material_price'));
        $this->materialCost = formatTaka($this->materialPrice * $costRate);
        $this->grossCost = formatTaka($this->serviceCost + $this->materialCost);
        $this->totalPriceWithoutVat = formatTaka($this->servicePrice + $this->materialPrice);
        //$this->totalPrice = formatTaka($this->totalPriceWithoutVat + $this->vat); // later
        $this->totalPrice = formatTaka($this->totalPriceWithoutVat);
        $this->grossPrice = formatTaka($this->totalPrice - $this->discount);
        $this->service_unit_price = formatTaka($this->service_unit_price);
        return $this;
    }

    public function code()
    {
        $startFrom = 16000;
        return sprintf('%08d', $this->id + $startFrom);
    }

    public function fullCode()
    {
        return  $this->partner_order->code() . '-' . $this->code();
    }
}
