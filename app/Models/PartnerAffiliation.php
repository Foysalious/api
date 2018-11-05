<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PartnerAffiliation extends Model
{
    protected $guarded = ['id'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function partner()
    {
        return $this->hasOne(Partner::class, 'affiliation_id');
    }

    public function scopeSpCount($query, $ambassador_id)
    {
        return $query->leftJoin('affiliates', 'affiliates.id', '=', 'partner_affiliations.affiliate_id')
            ->whereRaw('partner_affiliations.affiliate_id IN ( SELECT id FROM affiliates WHERE ambassador_id = ? ) AND affiliates.under_ambassador_since < partner_affiliations.created_at', [$ambassador_id]);
    }

    public function scopeSuccessful($query)
    {
        return $query->status('successful');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public static function reward()
    {
        return constants('PARTNER_AFFILIATION_REWARD');
    }

    public static function partnerOrderBenchmark()
    {
        return constants('PARTNER_AFFILIATION_PARTNER_ORDER_BENCHMARK');
    }

    public function transactions()
    {
        return $this->morphMany(AffiliateTransaction::class, 'affiliation');
    }
}
