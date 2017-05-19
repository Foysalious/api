<?php namespace App\Models;

use Carbon\Carbon;
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

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * @param $customer_id
     * @return array
     */
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
        $promotion = Customer::find($customer_id)->promotions()->where('voucher_id', $this->id)->get();
        return $promotion == null ? false : $promotion->first();
    }
}
