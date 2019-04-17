<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrder extends Model
{
    protected $guarded = ['id'];

    public function calculate()
    {

    }

    public function customer()
    {
        return $this->belongsTo(PosCustomer::class);
    }

    public function items()
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PosOrderPayment::class);
    }
}
