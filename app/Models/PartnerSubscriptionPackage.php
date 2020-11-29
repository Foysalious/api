<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\PayableType;
use Sheba\Subscription\Partner\BillingType;
use Sheba\Subscription\SubscriptionPackage;

class PartnerSubscriptionPackage extends Model implements SubscriptionPackage,PayableType
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
        $types =  json_decode($this->rules, 1)['subscription_fee'];
        foreach ($types as $type)
            if ($type['title'] == $billing_type) return (double)$type['price'];

        return 0;
    }

    public function originalDuration($billing_type = 'monthly')
    {
        $types =  json_decode($this->rules, 1)['subscription_fee'];
        foreach ($types as $type)
            if ($type['title'] == $billing_type) return $type['duration'];

        return 1;
    }

    public function titleTypeBn($billing_type = 'monthly')
    {
        $types =  json_decode($this->rules, 1)['subscription_fee'];
        foreach ($types as $type)
            if ($type['title'] == $billing_type) return $type['title_bn'];

        return '';
    }

    public function discountPrice($billing_type = 'monthly', $billing_cycle = 1)
    {
        if ($running_discount = $this->runningDiscount($billing_type)) {
            if (in_array($billing_cycle, json_decode($running_discount->applicable_billing_cycles))) {
                if ($running_discount->is_percentage) return $this->originalPrice($billing_type) * $running_discount->amount;
                else return $running_discount->amount;
            }
        } else {
            return 0;
        }
    }

    public function discountPriceFor($discount_id)
    {
        $this->load(['discounts' => function ($query) use ($discount_id) {
            return $query->where('id', $discount_id);
        }]);

        $discount = $this->discounts ? $this->discounts->first() : null;
        if ($discount) {
            if ($discount->is_percentage) return $this->originalPrice($discount->billing_type) * $discount->amount;
            else return $discount->amount;
        } else {
            return 0;
        }
    }

    public function originalPricePerDay($billing_type = 'monthly')
    {
        $day = $this->originalDuration($billing_type);
        return $this->originalPrice($billing_type) / $day;
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
        return json_decode($this->new_rules);
    }

    public function getCommissionAttribute()
    {
        return $this->rules()->commission->value;
    }

    public function getResourceCapAttribute()
    {
        return (int)$this->rules()->resource_cap->value;
    }

    public function getAccessRules()
    {
        return json_decode($this->new_rules, 1)['access_rules'];
    }
}
