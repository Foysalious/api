<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Subscription\Package;
use Sheba\Subscription\SubscriptionPackage;

class PartnerSubscriptionPackage extends Model implements SubscriptionPackage
{
    protected $guarded = ['id'];
    protected $table = 'partner_subscription_packages';
    protected $dates = ['activate_from'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function discounts()
    {
        return $this->hasMany(PartnerSubscriptionPackageDiscount::class, 'package_id', 'id');
    }

    public function scopeValidDiscounts()
    {
        return $this->with(['discounts' => function ($query) {
            return $query->valid();
        }]);
    }

    public function originalPrice($billing_type = 'monthly')
    {
        return (double)json_decode($this->rules, 1)['fee'][$billing_type]['value'];
    }

    public function discountPrice($billing_type = 'monthly', $billing_cycle = 1)
    {
        if ($running_discount = $this->runningDiscount($billing_type)) {
            if (in_array($billing_cycle, $running_discount->applicable_billing_cycles)) {
                if ($running_discount->is_percentage) return $this->originalPrice($billing_type) * $running_discount->amount;
                else $running_discount->amount;
            }
        } else {
            return 0;
        }
    }

    public function originalPricePerDay($billing_type = 'monthly')
    {
        $day = $billing_type == 'monthly' ? 30 : 365;
        return $this->originalPrice() / $day;
    }

    public function runningDiscount($billing_type = 'monthly')
    {
        $this->load(['discounts' => function ($q) use ($billing_type) {
            $q->valid()->type($billing_type);
        }]);
        return $this->discounts ? $this->discounts->first() : null;
    }

    private function rules()
    {
        return json_decode($this->rules);
    }

    public function getCommissionAttribute()
    {
        return $this->rules()->commission->value;
    }
}
