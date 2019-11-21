<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\LocationServiceDiscount\Model as LocationServiceDiscount;

class LocationService extends Model
{
    protected $table = 'location_service';
    public $timestamps = false;

    public function discounts()
    {
        return $this->hasMany(LocationServiceDiscount::class);
    }

}
