<?php namespace App\GraphQL\Query;

use App\Models\HyperLocal;

trait LocationFilter
{
    private function getLocationId($args)
    {
        if (!isset($args['location']) || !(isset($args['lat']) && isset($args['lng']))) return null;

        if (isset($args['location'])) return $args['location'];

        $hyper_location = HyperLocal::insidePolygon((double) $args['lat'], (double) $args['lng'])->first();

        return is_null($hyper_location) ? null : $hyper_location->location_id;
    }

    private function filterLocation($query, $location)
    {
        $query->whereHas('locations', function ($q) use ($location) {
            $q->where('locations.id', $location);
        });
    }
}
