<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubscription extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function isPercentage()
    {
        return (int)$this->is_discount_amount_percentage;
    }

    public function hasCap()
    {
        return $this->cap > 0;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getParentCategoryAttribute()
    {
        return $this->service->category->parent->id;
    }
}