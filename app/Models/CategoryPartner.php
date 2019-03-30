<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryPartner extends Model
{
    protected $guarded = ['id',];
    protected $table = 'category_partner';
    protected $casts = ['delivery_charge' => 'double'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function resources()
    {
        return $this->belongsTo(CategoryPartner::class);
    }

    public function deliveryChargeUpdateRequest()
    {
        return $this->hasMany(DeliveryChargeUpdateRequest::class);
    }

    public function needsShebaLogistic()
    {
        return $this->is_home_delivery_applied && $this->uses_sheba_logistic;
    }
}
