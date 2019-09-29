<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BusinessTripRequest extends Model
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
}