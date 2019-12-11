<?php namespace Sheba\Map;

use Sheba\Map\Client\BarikoiClient;
use Sheba\Map\Client\Client;
use Sheba\Location\Geo;

class GeoCode
{
    /** @var Address */
    private $address;
    /** @var Client */
    private $client;

    public function __construct()
    {
        $this->client = new BarikoiClient();

    }

    public function setAddress(Address $address)
    {
        $this->address = $address;
        return $this;
    }


    /**
     * @return Geo
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getGeo()
    {
        $geo = $this->client->getGeoFromAddress($this->address);
    }
}