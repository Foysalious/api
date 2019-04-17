<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PartnerPosServiceDiscount extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['cap' => 'double', 'amount' => 'double'];
    protected $dates = ['start_date', 'end_date'];

    public function partnerPosService()
    {
        return $this->belongsTo(PartnerPosService::class);
    }

    public function isPercentage()
    {
        return $this->is_amount_percentage;
    }

    public function hasCap()
    {
        return $this->cap > 0;
    }

    public function scopeRunningDiscounts($query)
    {
        return $query->where('end_date', '>=', Carbon::now());
    }

    public function scopeExpiredDiscounts($query)
    {
        return $query->where('end_date', '<', Carbon::now());
    }

    public function scopeRunningAt($query, Carbon $date)
    {
        $query->where(function ($query) use ($date) {
            $query->where('start_date', '<=', $date->toDateTimeString());
            $query->where('end_date', '>=', $date->toDateTimeString());
        });
    }

    public function scopeRunningBetween($query, Carbon $start_date, Carbon $end_date)
    {
        $query->where(function ($q) use ($start_date, $end_date) {
            $q->runningAt($start_date)
                ->orWhere(function ($q1) use ($end_date) {
                    $q1->runningAt($end_date);
                });
        });
    }

    public function scopeOf($query, $partner_pos_service_id)
    {
        $query->where('partner_pos_service_id', $partner_pos_service_id);
    }

    public function getIsRunningAttribute()
    {
        return Carbon::now()->lte($this->end_date);
    }

    public function getIsExpiredAttribute()
    {
        return Carbon::now()->gt($this->end_date);
    }
}
