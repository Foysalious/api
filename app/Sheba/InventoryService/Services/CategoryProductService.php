<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class CategoryProductService
{
    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    public function getProducts($partnerId)
    {
        $url = 'api/v1/partners/' . $partnerId . '/category-products';
        return $this->client->get($url);
    }

}