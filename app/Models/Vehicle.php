<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $guarded = ['id'];

    public function business()
    {
        return $this->morphTo();
    }

    public function basicInformations()
    {
        return $this->hasOne(VehicleBasicInformation::class);
    }

    public function registrationInformations()
    {
        return $this->hasOne(VehicleRegistrationInformation::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'current_driver_id');
    }

    public function businessTrip()
    {
        return $this->hasOne(BusinessTrip::class);
    }

}
