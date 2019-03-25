<?php namespace Sheba\Logistics\Repository;

class ParcelRepository extends BaseRepository
{
    public function getAll()
    {
        $result = $this->client->get('parcels');
        return !empty($result) ? $result['parcels'] : $result;
    }
}