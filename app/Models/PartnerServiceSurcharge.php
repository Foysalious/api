<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PartnerServiceSurcharge extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start_date', 'end_date'];
    protected $casts = ['amount' => 'double'];

    public function partnerService()
    {
        return $this->belongsTo(PartnerService::class);
    }

    public function scopeRunningAt($query, Carbon $date)
    {
        $query->where(function ($query) use ($date) {
            $query->where('start_date', '<=', $date->toDateTimeString());
            $query->where('end_date', '>=', $date->toDateTimeString());
        });
    }

    public function scopeRunningSurcharges($query)
    {
        return $query->where('end_date', '>=', Carbon::now());
    }

    public function scopeRunningBetween($query, Carbon $start_date, Carbon $end_date)
    {
        $query->where(function($q) use ($start_date, $end_date) {
            $q->runningAt($start_date)
                ->orWhere(function ($q1) use ($end_date) {
                    $q1->runningAt($end_date);
                });
        });
    }

    public function scopeOf($query, $partner_service_id)
    {
        $query->where('partner_service_id', $partner_service_id);
    }
}