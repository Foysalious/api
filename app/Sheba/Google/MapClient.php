<?php namespace Sheba\Google;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Sheba\Location\Geo;

class MapClient
{
    private $distanceMatrixKey;
    private $client;

    public function __construct()
    {
        $this->distanceMatrixKey = config('google.distance_matrix_key');
        $this->client = new Client();
    }

    public function getDistanceBetweenTwoPints(Geo $from_geo, Geo $to_geo)
    {
        $response = $this->client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json',
            [
                'query' => [
                    'origins' => (string)$from_geo->getLat() . ',' . (string)$from_geo->getLng(),
                    'destinations' => (string)$to_geo->getLat() . ',' . (string)$to_geo->getLng(),
                    'key' => $this->distanceMatrixKey,
                    'mode' => 'driving'
                ]
            ]);
        return json_decode($response->getBody());
    }
}