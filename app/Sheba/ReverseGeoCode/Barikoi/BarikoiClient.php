<?php namespace Sheba\ReverseGeoCode\Barikoi;


use GuzzleHttp\Exception\RequestException;
use Sheba\Location\Geo;
use GuzzleHttp\Client as HTTPClient;
use Sheba\ReverseGeoCode\Address;
use Sheba\ReverseGeoCode\Client;

class BarikoiClient implements Client
{
    private $apiKey;
    /** @var Geo */
    private $geo;

    public function __construct()
    {
        $this->apiKey = config('barikoi.api_key');
    }

    public function setGeo(Geo $geo): Client
    {
        $this->geo = $geo;
        return $this;
    }

    public function get(): Address
    {
        try {
            $client = new HTTPClient();
            $response = $client->request('GET', "https://barikoi.xyz/v1/api/search/reverse/geocode/" . $this->apiKey . "/place", [
                'query' => [
                    'latitude' => $this->geo->getLat(),
                    'longitude' => $this->geo->getLng(),
                ]
            ]);
            $response = json_decode($response->getBody());
            $address = new Address();
            if (!isset($response->Place)) return $address->setAddress(null);
            $place = $response->Place[0];
            return $address->setAddress($place->Address . ', ' . $place->area . ', ' . $place->city);
        } catch (RequestException $e) {
            throw $e;
        }
    }

}