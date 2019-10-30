<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobCancelReason extends Model
{
    protected $guarded = ['id'];

    public function scopeForCustomer($query)
    {
        return $query->where('is_published_for_customer', 1);
    }

    public function scopeAffectsPartnerPerformance($query)
    {
        return $query->where('affects_partner_performance',1);
    }
}