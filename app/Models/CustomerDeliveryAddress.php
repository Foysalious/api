<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDeliveryAddress extends Model {
    protected $table = 'customer_delivery_addresses';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}