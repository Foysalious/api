<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleRegistrationInformation extends Model
{
    protected $guarded = ['id'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
