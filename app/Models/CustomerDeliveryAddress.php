<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerDeliveryAddress extends Model
{
    use SoftDeletes;

    protected $table = 'customer_delivery_addresses';
    protected $fillable = ['name', 'mobile', 'address'];
    protected $dates = ['deleted_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}