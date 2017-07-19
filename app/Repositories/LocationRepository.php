<?php

namespace App\Repositories;


use App\Models\Location;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class LocationRepository
{

    public function getLocationFromLatLng($latlng)
    {
        $result_type = array('sublocality', 'political', 'sublocality_level_1');
        $first_result_type = array('neighborhood', 'political');
        $client = new Client();
        try {
            $res = $client->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json',
                ['query' =>
                    [
                        'latlng' => $latlng
                    ],
                    [
                        'key' => env('GOOGLE_API_KEY')
                    ],
                    [
                        'result_type' => $result_type
                    ]
                ]);
        } catch (RequestException $e) {
            return null;
        }
        $results = json_decode($res->getBody())->results;
    }
}