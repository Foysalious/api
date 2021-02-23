<?php namespace App\Sheba\InventoryService\Repository;


class UnitRepository extends  BaseRepository
{

    /**
     *
     * @return array|object|string|null
     */

    public function getallunits()
    {
        $url = 'api/v1/units';
        return $this->client->get($url);
    }

}