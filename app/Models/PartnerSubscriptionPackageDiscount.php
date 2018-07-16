<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PartnerSubscriptionPackageDiscount extends Model
{
    protected $guarded = ['id'];
    protected $table = 'partner_subscription_discounts';
    protected $dates = ['start_date', 'end_date'];
    protected $casts = ['amount' => 'double', 'is_percentage' => 'double'];
    public $timestamps = false;

    public function package()
    {
        return $this->belongsTo(PartnerSubscriptionPackage::class);
    }

    public function scopeValid($query)
    {
        return $query->where([['start_date', '<=', Carbon::now()], ['end_date', '>=', Carbon::now()]]);
    }

    public function scopeType($query, $billing_type)
    {
        return $query->where('billing_type', $billing_type);
    }
}
