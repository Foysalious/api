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
}