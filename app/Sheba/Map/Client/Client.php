<?php namespace Sheba\Map\Client;

use Sheba\Location\Geo;
use Sheba\Map\Address;

interface Client
{
    public function getAddressFromGeo(Geo $geo): Address;

    public function getGeoFromAddress(Address $address): Geo;
}