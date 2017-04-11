<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start_date', 'end_date'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function usage($customer)
    {
        return $this->orders->where('customer_id', $customer)->count();
    }
}
