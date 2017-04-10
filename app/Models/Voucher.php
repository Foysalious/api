<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $guarded = ['id'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function usage($customer)
    {
        return $this->orders->where('customer_id', $customer)->count();
    }
}
