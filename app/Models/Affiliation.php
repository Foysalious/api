<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    protected $guarded = ['id'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function statusChangeLogs()
    {
        return $this->hasMany(AffiliationStatusChangeLog::class);
    }

    public function transactions()
    {
        return $this->hasMany(AffiliateTransaction::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'successful');
    }

    public function scopeInPreviousMonth($query)
    {
        return $query->whereMonth('created_at', '=', Carbon::yesterday()->month)
            ->whereYear('created_at', '=', Carbon::yesterday()->year);
    }

    public function scopeInYesterday($query)
    {
        return $query->whereDate('created_at', '=', Carbon::yesterday());
    }

}
