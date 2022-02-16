<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sheba\Location\Geo;

class CustomerDeliveryAddress extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'customer_delivery_addresses';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeHasGeo($query)
    {
        return $query->where('geo_informations', '<>', null);
    }

    public function scopeIsSaved($query)
    {
        return $query->where('is_saved', 1);
    }

    public function getGeoAttribute()
    {
        return $this->geo_informations ?
            (is_string($this->geo_informations) ? json_decode($this->geo_informations) : $this->geo_informations) : 
            null;
    }

    public function getGeo()
    {
        $geo = $this->getGeoAttribute();
        return new Geo($geo->lat, $geo->lng);
    }

}
