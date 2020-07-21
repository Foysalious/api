<?php namespace Sheba\Map\Client;

use Sheba\Location\Geo;
use Sheba\Map\Address;
use Sheba\Map\Distance;

interface Client
{
    public function getAddressFromGeo(Geo $geo): Address;

    public function getGeoFromAddress(Address $address): Geo;

    public function getDistanceBetweenTwoPoints(Geo $from, Geo $to) : Distance;
}