<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Checkout\SubscriptionOrderInterface;

class SubscriptionOrder extends Model implements SubscriptionOrderInterface
{
    protected $guarded = ['id'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function schedules()
    {
        return json_decode($this->schedules);
    }
}
