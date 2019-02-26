<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubscription extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getParentCategoryAttribute()
    {
        return $this->service->category->parent->id;
    }

    public function discounts()
    {
        return $this->hasMany(ServiceSubscriptionDiscount::class);
    }
}