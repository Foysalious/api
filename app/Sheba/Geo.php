<?php

namespace App\Sheba;


use GuzzleHttp\Client;

class Geo
{
    public function geoCodeFromPlace($place)
    {
        $client = new Client();
        $url = "https://maps.googleapis.com/maps/api/geocode/json?region=bd&key=" . config('google.map_key') . "&address=" . trim($place);
        $res = $client->request('GET', $url);
        $data = json_decode($res->getBody());
        if ($data->status == "OK") {
            $result = $data->results[0];
            $location_data = $result->geometry->location;
            return ['lat' => (double)$location_data->lat, 'lng' => (double)$location_data->lng];
        } else {
            return null;
        }
    }
}