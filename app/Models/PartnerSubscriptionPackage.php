<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PartnerSubscriptionPackage extends Model
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
        return $this->belongsTo(PartnerSubscriptionPackageDiscount::class, 'id', 'package_id');
    }

    public function scopeValidDiscount()
    {
        return $this->with(['discount' => function($query) {
            return $query->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now());
        }]);
    }
}
