<?php namespace Sheba\Map;


use Sheba\Map\Client\BarikoiClient;
use Sheba\Map\Client\Client;
use Sheba\Location\Geo;

class ReverseGeoCode
{
    /** @var Geo */
    private $geo;
    /** @var Client */
    private $client;

    public function __construct(BarikoiClient $bari_koi_client)
    {
        $this->client = $bari_koi_client;

    }

    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }


    public function getAddress()
    {
        return $this->client->getAddressFromGeo($this->geo);
    }
}