<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerSubscriptionPackageCharge extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['activation_date', 'billing_date'];

    public function partner()
    {
        return $this->hasOne(Partner::class, 'id', 'partner_id');
    }
}
