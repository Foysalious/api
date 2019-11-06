<?php namespace Sheba\Barikoi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Sheba\Location\Geo;

class BarikoiClient
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('barikoi.api_key');
    }

    public function getAddress(Geo $geo)
    {
        try {
            $client = new Client();
            $response = $client->request('GET', "https://barikoi.xyz/v1/api/search/reverse/geocode/" . $this->apiKey . "/place", [
                'query' => [
                    'latitude' => $geo->getLat(),
                    'longitude' => $geo->getLng(),
                ]
            ]);
            $response = json_decode($response->getBody());
            return isset($response->Place) ? $response->Place[0]->Address : null;
        } catch (RequestException $e) {
            throw $e;
        }
    }
}