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

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function calculate()
    {
        $o_statuses = constants('ORDER_STATUSES');
        $po_statuses = constants('PARTNER_ORDER_STATUSES');

        $total_partner_orders = 0;
        $po_status_counter = [
            $po_statuses['Open'] => 0,
            $po_statuses['Process'] => 0,
            $po_statuses['Closed'] => 0,
            $po_statuses['Cancelled'] => 0
        ];
        $this->totalPrice = 0;
        $this->due = 0;
        foreach ($this->partner_orders as $partnerOrder) {
            $partnerOrder->calculate();
            $this->totalPrice += $partnerOrder->grossAmount;
            $this->due += $partnerOrder->due;
            $po_status_counter[$partnerOrder->status]++;
            $total_partner_orders++;
        }

        if ($po_status_counter[$po_statuses['Open']] == $total_partner_orders) {
            $this->status = $o_statuses['Open'];
        } else if ($po_status_counter[$po_statuses['Cancelled']] == $total_partner_orders) {
            $this->status = $o_statuses['Cancelled'];
        } else if ($po_status_counter[$po_statuses['Closed']] == $total_partner_orders) {
            $this->status = $o_statuses['Closed'];
        } else if ($po_status_counter[$po_statuses['Open']] + $po_status_counter[$po_statuses['Cancelled']] == $total_partner_orders) {
            $this->status = $o_statuses['Open'];
        } else if ($po_status_counter[$po_statuses['Closed']] + $po_status_counter[$po_statuses['Cancelled']] == $total_partner_orders) {
            $this->status = $o_statuses['Closed'];
        } else {
            $this->status = $o_statuses['Process'];
        }

        return $this;
    }

    public function channelCode()
    {
        if (in_array($this->sales_channel, ['Web', 'Call-Center', 'App', 'Facebook'])) {
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
        $startFrom = 8000;
        return $this->channelCode() . '-' . sprintf('%06d', $this->id + $startFrom);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

}
