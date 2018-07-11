<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerSubscriptionPackageDiscount extends Model
{
    protected $guarded = ['id'];
    protected $table = 'partner_subscription_discounts';
    protected $dates = ['start_date', 'end_date'];
    public $timestamps = false;

    public function package()
    {
        return $this->belongsTo(PartnerSubscriptionPackage::class);
    }
}
