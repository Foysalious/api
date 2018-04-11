<?php namespace Sheba\Location\Distance\Strategies;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Sheba\Location\Coords;
use Sheba\Location\Distance\DistanceCalculator;

class GoogleDistanceMatrix extends DistanceCalculator
{
    private $key;

    private $origins;
    private $destinations;

    public function __construct()
    {
        $this->key = env('GOOGLE_DISTANCEMATRIX_KEY');
    }

    public function distance()
    {
        $this->origins = $this->formatCoords([$this->from]);
        $this->destinations = $this->formatCoords([$this->to]);
        $result = $this->get();
        if($result->status != "OK") return null;

        $element = $result->rows[0]->elements[0];
        if($element->status != "OK") return null;

        return $element->distance->value;
    }

    public function distanceFromArray($from_array, $to_array)
    {
        $this->origins = $this->formatCoords($from_array);
        $this->destinations = $this->formatCoords($to_array);
        $result = $this->get();
        if($result->status != "OK") return null;

        $data = [];
        foreach ($result->rows as $i => $row) {
            foreach ($row->elements as $j => $element) {
                $data[$i][$to_array[$j]->id ?: $j] = $element->status == "OK" ? $element->distance->value : null;
            }
        }
        return $data;
    }

    private function formatCoords($coords_array)
    {
        $coords_formatted = '';
        foreach ($coords_array as $coords) { /* @var $coords Coords */
            $coords_formatted .= "$coords->lat,$coords->lng|";
        }
        return rtrim($coords_formatted, "|");
    }

    private function get()
    {
        $client = new Client();
        try {
            $res = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json', [
                'query' => [
                    'origins' => $this->origins,
                    'destinations' => $this->destinations,
                    'key' => $this->key
                ]
            ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return null;
        }
    }
}