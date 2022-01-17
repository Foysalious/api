<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class WarrantyUnitService
{
    protected $partner_id, $client;

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    public function getWarrantyUnitList()
    {
        return $this->client->get('api/v1/warranty-units');
    }
}