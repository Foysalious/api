<?php namespace App\Models;

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
        return $query->whereRaw('((NOW() BETWEEN start_date AND end_date) OR (NOW() >= start_date AND end_date IS NULL))');
    }
}
