<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PartnerPosService extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['price' => 'double', 'stock' => 'double'];

    public function category()
    {
        return $this->belongsTo(PosCategory::class);
    }

    public function subCategory()
    {
        return $this->category()->with('parent');
    }

    /**
     * Scope a query to only include a specific master category.
     *
     * @param Builder $query
     * @param $master_category_id
     * @return Builder
     */
    public function scopeOfParentCategory($query, $master_category_id)
    {
        return $query->whereHas('category', function ($q) use ($master_category_id) {
            $q->where('parent_id', $master_category_id);
        });
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopePartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id);
    }

    public function discounts()
    {
        return $this->hasMany(PartnerPosServiceDiscount::class);
    }

    public function discount()
    {
        return $this->runningDiscounts()->first();
    }

    public function runningDiscounts()
    {
        $now = Carbon::now();
        return $this->discounts()->where(function ($query) use ($now) {
            $query->where('start_date', '<=', $now);
            $query->where('end_date', '>=', $now);
        })->get();
    }

    public function getDiscountedAmount()
    {
        $discount = $this->discount();
        if ($discount->is_amount_percentage) {
            $calculated_discount_amount = ($this->price * $discount->amount) / 100;
            if ($discount->hasCap()) {
                $calculated_discount_amount = ($calculated_discount_amount > $discount->cap) ? $discount->cap : $calculated_discount_amount;
            }
            $amount = $this->price - $calculated_discount_amount;
        } else {
            $amount = $this->price - $discount->amount;
        }

        return ($amount < 0) ? 0 : (float)$amount;
    }
}
