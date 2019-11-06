<?php namespace Sheba\ReverseGeoCode;

use Sheba\Location\Geo;

class ReverseGeoCode
{
    /** @var Client */
    private $client;
    private $geo;

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }

    /**
     * @return Address
     */
    public function get()
    {
        return $this->client->setGeo($this->geo)->get();
    }
}