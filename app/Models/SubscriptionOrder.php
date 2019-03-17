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

    public function deliveryAddress()
    {
        return $this->hasOne(CustomerDeliveryAddress::class, 'id', 'delivery_address_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function channelCode()
    {
        if (in_array($this->sales_channel, ['Web', 'Call-Center', 'App', 'Facebook', 'App-iOS', 'E-Shop'])) {
            $prefix = 'D';
        } elseif ($this->sales_channel == 'B2B') {
            $prefix = 'F';
        } elseif ($this->sales_channel == 'Store') {
            $prefix = 'S';
        } else {
            $prefix = 'A';
        }
        return $prefix;
    }

    public function code()
    {
        return $this->channelCode() . '-' . sprintf('%06d', $this->id);
    }
}
