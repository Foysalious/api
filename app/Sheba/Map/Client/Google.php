<?php namespace Sheba\Map\Client;


use GuzzleHttp\Exception\GuzzleException;
use Sheba\Location\Geo;
use Sheba\Map\Address;
use GuzzleHttp\Client as HTTPClient;
use Sheba\Map\Distance;
use Sheba\Map\MapClientNoResultException;

class Google implements Client
{
    public function getAddressFromGeo(Geo $geo): Address
    {
        // TODO: Implement getAddressFromGeo() method.
    }

    public function getGeoFromAddress(Address $address): Geo
    {
        // TODO: Implement getGeoFromAddress() method.
    }

    /**
     * @param Geo $from
     * @param Geo $to
     * @return Distance
     * @throws MapClientNoResultException
     * @throws GuzzleException
     */
    public function getDistanceBetweenTwoPoints(Geo $from, Geo $to): Distance
    {
        $client = new HTTPClient();
        $response = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json',
            [
                'query' => [
                    'origins' => (string)$from->getLat() . ',' . (string)$from->getLng(),
                    'destinations' => (string)$to->getLat() . ',' . (string)$to->getLng(),
                    'key' => config('google.distance_matrix_key'),
                    'mode' => 'driving'
                ]
            ]);
        $data = json_decode($response->getBody());
        if ($data->rows[0]->elements[0]->status == 'ZERO_RESULTS') throw new MapClientNoResultException('Invalid Address');
        $distance = new Distance();
        $distance->setDistanceInKms((double)($data->rows[0]->elements[0]->distance->value) / 1000)
            ->setDurationInMinutes((double)($data->rows[0]->elements[0]->duration->value) / 60);
        return $distance;
    }
}