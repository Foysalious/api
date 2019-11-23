<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\LocationServiceDiscount\Model as LocationServiceDiscount;

class LocationService extends Model
{
    public $timestamps = false;
    protected $table = 'location_service';

    public function discounts()
    {
        return $this->hasMany(LocationServiceDiscount::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
