<?php namespace Sheba\Map;

use GuzzleHttp\Exception\GuzzleException;
use Sheba\Map\Client\BarikoiClient;
use Sheba\Map\Client\Client;

class GeoCode
{
    /** @var Address */
    private $address;
    /** @var Client */
    private $client;

    public function __construct(BarikoiClient $barikoiClient)
    {
        $this->client = $barikoiClient;

    }

    public function setAddress(Address $address)
    {
        $this->address = $address;
        return $this;
    }


    /**
     * @throws GuzzleException
     * @throws MapClientNoResultException
     */
    public function getGeo()
    {
        return $this->client->getGeoFromAddress($this->address);
    }
}