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
        return $this->hasOne(Vehicle::class);
    }

}