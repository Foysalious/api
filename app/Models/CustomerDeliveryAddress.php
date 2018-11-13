<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerDeliveryAddress extends Model
{
    use SoftDeletes;

    protected $table = 'customer_delivery_addresses';

    protected $fillable = ['name', 'address'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    protected $dates = ['deleted_at'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

}