<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ServiceSubscriptionDiscount extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start_date', 'end_date'];
    protected $casts = ['discount_amount' => 'double', 'is_discount_amount_percentage' => 'int'];

    public function serviceSubscription()
    {
        return $this->belongsTo(ServiceSubscription::class);
    }

    public function scopeSubscriptionType($query, $subscription_type)
    {
        return $query->where('subscription_type', $subscription_type);
    }

    public function scopeValid($query)
    {
        return $query->where([
            ['start_date', '<=', Carbon::now()],
            ['end_date', '>=', Carbon::now()]
        ]);
    }

    public function isPercentage()
    {
        return (int)$this->is_discount_amount_percentage;
    }

    public function hasCap()
    {
        return $this->cap > 0;
    }
}