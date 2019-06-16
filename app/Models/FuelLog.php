<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelLog extends Model
{

    protected $guarded = ['id',];
    protected $dates = ['refilled_date'];
    protected $table = 'fuel_logs';

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopeFuelLogs($query, $business)
    {
        return $query->whereHas('vehicle', function ($query) use ($business) {
            $query->where('owner_id', $business->id);
        })->with('vehicle')->orderBy('id', 'DESC');
    }

    public function scopeTotalFuelCost($query, $start_date, $end_date, $business)
    {
        return $query->whereHas('vehicle', function ($query) use ($business) {
            $query->where('owner_id', $business->id);
        })->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])->sum('price');
    }

    public function scopeTotalLitres($query, $start_date, $end_date, $business)
    {
        return $query->whereHas('vehicle', function ($query) use ($business) {
            $query->where('owner_id', $business->id);
        })->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])
            ->where('unit', 'LIKE', 'ltr');
    }

    public function scopeTotalGallons($query, $start_date, $end_date, $business)
    {
        return $query->whereHas('vehicle', function ($query) use ($business) {
            $query->where('owner_id', $business->id);
        })->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])
            ->where('unit', 'LIKE', 'cubic_feet');
    }
}