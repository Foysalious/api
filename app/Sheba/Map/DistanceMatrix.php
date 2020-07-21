<?php namespace Sheba\Map;


use GuzzleHttp\Exception\GuzzleException;
use Sheba\Location\Geo;
use Sheba\Map\Client\Google;
use Sheba\Map\Client\Client;

class DistanceMatrix
{
    /** @var Client */
    private $client;

    public function __construct(Google $google)
    {
        $this->client = $google;
    }

    /**
     * @param Geo $from
     * @param Geo $to
     * @return Distance
     * @throws GuzzleException
     * @throws MapClientNoResultException
     */
    public function getDistanceMatrix(Geo $from, Geo $to)
    {
        return $this->client->getDistanceBetweenTwoPoints($from, $to);
    }
}