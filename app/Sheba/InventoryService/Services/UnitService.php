<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class UnitService
{

    /**
     *
     * @return array|object|string|null
     */
    public $client;
    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    public function getallunits()
    {
        $url = 'api/v1/units';
        return $this->client->get($url);
    }

    public function getWeightUnits()
    {
        $url = 'api/v1/weight-units';
        return $this->client->get($url);
    }

}
