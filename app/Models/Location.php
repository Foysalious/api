<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Sheba\Location\Geo;

class Location extends Model
{
    use HybridRelations;

    protected $guarded = ['id'];

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptionOrders()
    {
        return $this->hasMany(SubscriptionOrder::class);
    }

    public function custom_orders()
    {
        return $this->hasMany(CustomOrder::class);
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', 1);
    }

    public function scopeHasGeoInformation($query)
    {
        return $query->where('geo_informations', '<>', null);
    }

    public function scopeHasPolygon($query)
    {
        return $query->where('geo_informations', 'like', '%polygon%');
    }

    public function hyperLocal()
    {
        return $this->hasOne(HyperLocal::class);
    }

    public function customer_delivery_addresses()
    {
        return $this->hasMany(CustomerDeliveryAddress::class);
    }

    public function isPublished()
    {
        return (int)$this->publication_status;
    }

    /**
     * @return Geo
     */
    public function getCenter()
    {
        $lat = $lng = null;
        if ($this->geo_informations) {
            $geo = json_decode($this->geo_informations);
            $lat = isset($geo->center) ? $geo->center->lat : $geo->lat;
            $lng = isset($geo->center) ? $geo->center->lng : $geo->lng;
        }
        return new Geo($lat, $lng);
    }
}
