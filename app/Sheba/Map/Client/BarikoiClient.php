<?php namespace Sheba\Map\Client;

use App\Sheba\Map\BarikoiAddress;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Sheba\Map\Distance;
use Sheba\Map\MapClientNoResultException;
use Sheba\Location\Geo;
use GuzzleHttp\Client as HTTPClient;
use Sheba\Map\Address;

class BarikoiClient implements Client
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('barikoi.api_key');
    }

    public function getAddressFromGeo(Geo $geo): Address
    {
        try {
            $client = new HTTPClient();
            $response = $client->request('GET', 'https://barikoi.xyz/v1/api/search/reverse/geocode/server/' . $this->apiKey . '/place', [
                'query' => [
                    'latitude' => $geo->getLat(),
                    'longitude' => $geo->getLng(),
                    'address' => 'true',
                    'area' => 'true'
                ]
            ]);
            $response = json_decode($response->getBody());
            $address = new BarikoiAddress();
            if (!isset($response->place)) return $address->setAddress(null);
            $place = $response->place;
            return $address->handleFullAddress($place);
        } catch (RequestException $e) {
            throw $e;
        }
    }

    /**
     * @param Address $address
     * @return Geo
     * @throws GuzzleException
     * @throws MapClientNoResultException
     */
    public function getGeoFromAddress(Address $address): Geo
    {
        $client = new HTTPClient();
        $response = $client->request('POST', 'https://barikoi.xyz/v1/api/search/' . $this->apiKey . '/rupantor/geocode', [
            'form_params' => [
                'q' => $address->getAddress()
            ]
        ]);
        $response = json_decode($response->getBody());
        if (!isset($response->geocoded_address->latitude)) throw new MapClientNoResultException('Invalid Address');
        $geo = new Geo();
        return $geo->setLat($response->geocoded_address->latitude)->setLng($response->geocoded_address->longitude);
    }

    public function getDistanceBetweenTwoPoints(Geo $from, Geo $to) :Distance
    {
        // TODO: Implement getDistanceBetweenTwoPoints() method.
    }
}
