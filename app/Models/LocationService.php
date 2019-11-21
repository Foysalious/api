<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationService extends Model
{
    protected $table = 'location_service';
    protected $fillable = ['location_id', 'service_id', 'prices'];
    public $timestamps = false;

    public function discounts()
    {
//        return $this->hasMany()
    }
}
