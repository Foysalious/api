<?php


namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class WarrantyUnitService
{
    protected $partner_id, $client;

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $partner_id
     * @return WarrantyUnitService
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    public function getWarrantyUnitList()
    {
        return $this->client->get('api/v1/partners/' . $this->partner_id . '/warranty-unit');
    }
}