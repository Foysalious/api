<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $dates = ['valid_till'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function scopeValid($query)
    {
        return $query->where('is_valid', 1)->where('valid_till', '>=', Carbon::now());
    }

    public function scopeIsApplied($query, $customer_id, $voucher_id)
    {
        return $query->where('customer_id', $customer_id)->where('voucher_id', $voucher_id);
    }

    public function scopeAdded($query, $voucher_id)
    {
        return $query->where('voucher_id', $voucher_id);
    }
}