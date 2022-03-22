<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sheba\Voucher\VoucherUsageCalculator;

class Voucher extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $dates = ['start_date', 'end_date'];
    protected $casts = ['is_amount_percentage' => 'integer', 'cap' => 'double', 'amount' => 'double'];

    /** @var  VoucherUsageCalculator */
    private $usageCalculator;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->usageCalculator = new VoucherUsageCalculator();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function movieTicketOrders()
    {
        return $this->hasMany(MovieTicketOrder::class);
    }

    public function usage(Profile $profile)
    {
        return $this->usageCalculator->setVoucher($this)->usage($profile);
    }

    public function usedCustomerCount()
    {
        return $this->usageCalculator->setVoucher($this)->usedCustomerCount();
    }

    public function hasNotReachedMaxCustomer()
    {
        return $this->usedCustomerCount() < $this->max_customer;
    }

    public function hasNotReachedMaxOrder(Profile $profile)
    {
        return $this->usage($profile) < $this->max_order;
    }

    public function hasReachedMaxOrder(Profile $profile)
    {
        return $this->usage($profile) >= $this->max_order;
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

    /**
     * Scope a query to only include voucher.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeBySheba($query)
    {
        return $query->where('is_created_by_sheba', 1);
    }

    public function scopeByPartner($query, Partner $partner)
    {
        return $query->where('owner_type', "App\\Models\\Partner")->where('owner_id', $partner->id)->where('is_referral', 0);
    }

    /**
     * Scope a query to only include voucher.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeValid($query)
    {
        return $query->whereRaw('((NOW() BETWEEN start_date AND end_date) OR (NOW() >= start_date AND end_date IS NULL))')->where('is_active', 1);
    }

    public function scopeDateExpire($query)
    {
        return $this->isDateExpireQuery($query);
    }

    public function scopeNotValid($query)
    {
        return $this->isDateExpireQuery($query)->orWhere('is_active', 0);
    }

    public function isDateExpireQuery($query)
    {
        return $query->whereRaw('((NOW() NOT BETWEEN start_date AND end_date) OR (NOW() <= start_date AND end_date IS NULL))');
    }

    public function scopeSearch($query, $code)
    {
        return $query->where('code', 'like', '%' . strtoupper($code) . '%');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function getTagListAttribute()
    {
        return $this->tags->pluck('id')->toArray();
    }

    public function getTagNamesAttribute()
    {
        return $this->tags->pluck('name');
    }

    public function isValid()
    {
        return Carbon::now()->lessThanOrEqualTo($this->end_date) && $this->isActive();
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function usedCount()
    {
        return PosOrder::where('voucher_id', $this->id)->count();
    }
}
