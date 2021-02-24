<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;


class ProductService
{
    /**
     * @param $partner_id
     * @return array|object|string|null
     */

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    public function getAllProducts($partner_id)
    {
        $url = 'api/v1/partners/' . $partner_id . '/products';
        return $this->client->get($url);

    }

}