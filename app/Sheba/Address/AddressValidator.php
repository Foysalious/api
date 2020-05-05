<?php

namespace App\Sheba\Address;

use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use Sheba\Location\Geo;

class AddressValidator
{
    CONST THRESHOLD_DISTANCE = 40;

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
        $address = null;
        foreach ($results as $key => $value) {
            $address = $addresses->where('id', $key)->first();
            break;
        }
        return $address ? $address : 0;
    }

    public function isAddressNameExists($addresses, $new_address)
    {
        foreach ($addresses as $address) {
            similar_text($address->address, $new_address, $percent);
            if ($percent >= 80) return $address;
        }
        return 0;
    }

    public function isSameAddress(Geo $geo, Coords $current)
    {
        $to = [new Coords(floatval($geo->getLat()), floatval($geo->getLng()))];
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
        $results = collect($distance->from([$current])->to($to)->sortedDistance()[0])->reject(function ($value) {
            return $value > self::THRESHOLD_DISTANCE;
        });
        return count($results) > 0;
    }
}