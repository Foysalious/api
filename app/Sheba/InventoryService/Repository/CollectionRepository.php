<?php


namespace App\Sheba\InventoryService\Repository;


class CollectionRepository extends BaseRepository
{
    public function getAllCollection($partner_id)
    {
        $url = 'api/v1/partners/' . $partner_id . '/collection';
        return $this->client->get($url);
    }
}