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
            '$centerSphere' => [
                [
                    (double)$geo_info->lng,
                    (double)$geo_info->lat
                ],
                (double)$geo_info->radius * 1000
//                / 6378.16
            ]
        ]);
    }
}