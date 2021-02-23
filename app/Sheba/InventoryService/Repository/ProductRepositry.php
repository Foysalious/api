<?php namespace App\Sheba\InventoryService\Repository;


class ProductRepositry extends BaseRepository
{

    /**
     * @param $partner_id
     * @return array|object|string|null
     */
    public function getAllProducts($partner_id)
    {
        $url = 'api/v1/partners/'.$partner_id.'/products';
        return $this->client->get($url);
    }

}