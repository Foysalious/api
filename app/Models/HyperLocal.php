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
}