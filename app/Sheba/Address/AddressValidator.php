<?php

namespace App\Sheba\Address;


use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;

class AddressValidator
{
    CONST THRESHOLD_DISTANCE = 25;

    public function isAddressLocationExists($addresses, Coords $current)
    {
        $to = $addresses->reject(function ($address) {
            return $address->geo_informations == null;
        })->map(function ($address) {
            $geo = json_decode($address->geo_informations);
            return new Coords(floatval($geo->lat), floatval($geo->lng), $address->id);
        })->toArray();
        if (count($to) == 0) return 0;
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
        $results = collect($distance->from([$current])->to($to)->sortedDistance()[0])->reject(function ($value) {
            return $value > self::THRESHOLD_DISTANCE;
        });
        return $results->count() > 0 ? 1 : 0;
    }

    public function isAddressNameExists($addresses, $new_address)
    {
        foreach ($addresses as $address) {
            similar_text($address->address, $new_address, $percent);
            if ($percent >= 80) return 1;
        }
        return 0;
    }
}