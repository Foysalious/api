<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    public function jobs()
    {
        return $this->hasManyThrough(Job::class, PartnerOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function partner_orders()
    {
        return $this->hasMany(PartnerOrder::class);
    }

    public function calculate()
    {
        foreach($this->partnerOrders as $partnerOrder) {
            $partnerOrder->calculate();
        }
        return $this;
    }

}
