<?php namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

/**
 * @method static insidePolygon(float $lat, float $lng)
 * @method static insideCircle($geo_info)
 */
class HyperLocal extends Eloquent
{
    protected $connection = 'mongodb';

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeInsidePolygon($query, $lat, $lng)
    {
        return $query->where('geometry', 'geoIntersects', [
            '$geometry' => [
                'type' => 'Point',
                'coordinates' => [(double)$lng, (double)$lat],
            ],
        ]);
    }

    public function scopeInsideCircle($query, $geo_info)
    {
        return $query->where('geometry', 'nearSphere', [
            '$geometry' => [
                'type' => "Point",
                'coordinates' => [(double)$geo_info->lng, (double)$geo_info->lat]
            ],
            '$maxDistance' => (double)($geo_info->radius) * 1000
        ]);
    }
}
