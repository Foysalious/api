<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class Location extends Model
{
    use HybridRelations;
    protected $fillable = [
        'name',
        'city_id',
        'publication_status'
    ];

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
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

    public function hyperLocal()
    {
        return $this->hasOne(HyperLocal::class);
    }

    public function customer_delivery_addresses()
    {
        return $this->hasMany(CustomerDeliveryAddress::class);
    }

}
