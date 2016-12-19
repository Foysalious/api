<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $totalPrice;
    public $due;

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
        $this->totalPrice = 0;
        $this->due = 0;
        foreach ($this->partner_orders as $partnerOrder) {
            $partnerOrder->calculate();
            $this->totalPrice += $partnerOrder->totalPrice;
            $this->due += $partnerOrder->due;
        }
        return $this;
    }

}
