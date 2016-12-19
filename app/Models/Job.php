<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $materialPivotColumns = ['id', 'material_name', 'material_price', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];
    protected $guarded = ['id'];

    public $materialPrice;
    public $materialCost;
    public $grossCost;
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
        $this->materialPrice = formatTaka($this->usedMaterials()->sum('material_price'));
        $this->materialCost = formatTaka($this->materialPrice);
        $this->grossCost = formatTaka($this->service_cost + $this->materialCost);
        $this->totalPrice = formatTaka($this->service_price + $this->materialPrice);
        $this->grossPrice = formatTaka($this->totalPrice - $this->discount);
        return $this;
    }
}
