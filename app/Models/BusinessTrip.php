<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTrip extends Model
{
    protected $guarded = ['id'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function getTripReadableTypeAttribute()
    {
        return title_case(str_replace('_', ' ', $this->trip_type));
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'open')->orWhere('status', 'process');
    }

}