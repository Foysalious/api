<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarRentalJobDetail extends Model
{
    protected $guarded = ['id'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function pickUpLocation()
    {
        return $this->morphTo();
    }

    public function destinationLocation()
    {
        return $this->morphTo();
    }
}
