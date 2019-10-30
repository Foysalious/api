<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateTransaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function affiliation()
    {
        return $this->morphTo();
    }

    public function scopeEarning($query)
    {
        return $query->where([
            ['type', '=', 'Credit'],
            ['log', 'NOT LIKE', '%Moneybag Refilled%'],
            ['log', 'NOT LIKE', '%Manually Received%']
        ]);
    }
}
