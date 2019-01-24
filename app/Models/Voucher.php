<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start_date', 'end_date'];
    protected $casts = ['is_amount_percentage' => 'integer', 'cap' => 'double', 'amount' => 'double'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function usage($customer)
    {
        return $this->orders->where('customer_id', $customer)->count();
    }

    public function usedCustomerCount()
    {
        return $this->orders->groupBy('customer_id')->count();
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function validityTimeLine($customer_id)
    {
        if ($this->is_referral) {
            $promotion = $this->activatedPromo($customer_id);
            if (!$promotion)
                return [Carbon::today(), Carbon::tomorrow()];
            return [$promotion->created_at, $promotion->valid_till];
        }
        return [$this->start_date, $this->end_date];
    }

    private function activatedPromo($customer_id)
    {
        $customer = Customer::find($customer_id);
        if (!$customer) return false;
        $promotion = $customer->promotions()->where('voucher_id', $this->id)->get();
        return $promotion == null ? false : $promotion->first();
    }

    public function ownerIsCustomer()
    {
        return $this->owner_type == "App\\Models\\Customer";
    }

    public function ownerIsAffiliate()
    {
        return $this->owner_type == "App\\Models\\Affiliate";
    }

    public function scopeValid($query)
    {
        return $query->where([
            ['valid_till', '>=', Carbon::now()],
            ['is_valid', 1]
        ]);
    }
}
