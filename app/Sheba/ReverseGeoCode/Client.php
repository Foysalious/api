<?php namespace Sheba\ReverseGeoCode;

use Sheba\Location\Geo;

interface Client
{
    public function setGeo(Geo $geo): Client;

    public function get(): Address;
}