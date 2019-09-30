<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HiredVehicle extends Model
{
    protected $guarded = ['id'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope a query to only include voucher.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $now = Carbon::now()->toDateTimeString();
        return $query->whereRaw("(('$now' BETWEEN start AND end) OR ('$now' >= start AND end IS NULL))");
    }

    public function scopeHiredByBusiness($query, $business_id)
    {
        return $query->where([['hired_by_type', "App\\Models\\Business"], ['hired_by_id', $business_id]]);
    }
}
