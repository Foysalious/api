<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $guarded = ['id'];


    public function profile()
    {
        return $this->hasOne(Profile::class,'driver_id');
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'current_driver_id');
    }

    public function businessTrip()
    {
        return $this->hasOne(BusinessTrip::class);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

}