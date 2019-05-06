<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $guarded = ['id'];


    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class);
    }

}