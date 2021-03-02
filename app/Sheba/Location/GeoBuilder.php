<?php namespace Sheba\Location;

use GuzzleHttp\Client;

class GeoBuilder
{
    private $httpClient;
    private $baseUrl;
    private $mapKey;

    public function __construct(Client $client)
    {
        $this->httpClient = $client;
        $this->baseUrl = "https://maps.googleapis.com/maps/api/geocode/json?region=bd";
        $this->mapKey = config('google.map_key');
        $this->baseUrl .= "&key=" . $this->mapKey;
    }

    /**
     * @param $address
     * @return Geo
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fromAddressString($address)
    {
        $url = $this->baseUrl . "&address=" . trim($address);
        $res = $this->httpClient->request('GET', $url);
        $data = json_decode($res->getBody());
        $geo = new Geo();
        if ($data->status != "OK") return $geo;

        $result = $data->results[0];
        $location_data = $result->geometry->location;
        $geo->setLat($location_data->lat)->setLng($location_data->lng);
        return $geo;
    }
}