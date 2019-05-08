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

}