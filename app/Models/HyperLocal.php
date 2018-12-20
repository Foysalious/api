<?php

namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

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
        return $query->where('geometry', 'geoWithin', [
            '$centerSphere' => [[$geo_info->lng, $geo_info->lat], $geo_info->radius / 3963.2]
        ]);
    }
}