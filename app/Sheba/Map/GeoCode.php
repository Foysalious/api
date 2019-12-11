<?php namespace Sheba\Map;

use Sheba\Map\Client\BarikoiClient;
use Sheba\Map\Client\Client;

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
     * @return \Sheba\Location\Geo
     */
    public function getGeo()
    {
        return $this->client->getGeoFromAddress($this->address);
    }
}