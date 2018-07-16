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

    public function discount()
    {
        return $this->hasMany(PartnerSubscriptionPackageDiscount::class, 'package_id', 'id');
    }

    public function scopeValidDiscount()
    {
        return $this->with(['discount' => function($query) {
            return $query->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now());
        }]);
    }

    public function originalPrice($billing_type = 'monthly')
    {
        return json_decode($this->rules, 1)['fee'][$billing_type]['value'];
    }
}
