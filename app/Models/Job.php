<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model {

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function partner_order()
    {
        return $this->belongsTo(PartnerOrder::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function paymentLogs()
    {
        return $this->hasMany(JobPaymentLog::class);
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function usedMaterials()
    {
        return $this->hasMany(JobMaterial::class);
    }

    public function materialCost()
    {
        return $this->usedMaterials()->sum('material_price');
    }

    public function totalCost()
    {
        return $this->service_cost + $this->materialCost();
    }

    public function grossCost()
    {
        return $this->totalCost() - $this->discount;
    }
}
